<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

/**
 * Lands here when the OS opens `{scheme}://oauth/{action}` — the deep
 * link the heartdevs.com web OAuth callback redirects to (see
 * He4rt\Identity\Auth\Support\MobileOAuthDeepLink and
 * docs/plans/2026-09-22-api-mobile-jwt.md on that repo).
 *
 * `{action}` is a route segment ("callback" or "error"); `code`/`error`
 * arrive as query string on the deep link, which NativePHP deliberately
 * does NOT surface as route params (utm-tag style query strings must not
 * leak into {param} bindings) — so we parse them off the raw current URI
 * ourselves instead of relying on $this->param().
 */
class OAuthCallback extends NativeComponent
{
    public string $action = 'callback';

    public function mount(): void
    {
        $query = $this->currentQuery();

        if ($this->action !== 'callback') {
            $this->bounceToLogin($this->describeError($query['error'] ?? null));

            return;
        }

        $code = $query['code'] ?? null;

        if (!is_string($code) || $code === '') {
            $this->bounceToLogin('Não recebemos o código de autenticação. Tente novamente.');

            return;
        }

        $response = (new He4rtApi)->exchange($code);

        if (!$response->successful()) {
            $this->bounceToLogin('Não foi possível concluir o login. Tente novamente.');

            return;
        }

        (new AuthTokenStore)->put(
            (string) $response->json('access_token'),
            (int) $response->json('expires_in'),
        );

        $this->replace('/home');
    }

    public function navTitle(): string
    {
        return '';
    }

    public function render(): View
    {
        return view('native.oauth-callback');
    }

    /** @return array<string, string> */
    private function currentQuery(): array
    {
        $uri = $this->nativeRouter?->currentUri() ?? '';
        $queryString = parse_url($uri, PHP_URL_QUERY);

        if (!is_string($queryString)) {
            return [];
        }

        parse_str($queryString, $query);

        /** @var array<string, string> $query */
        return $query;
    }

    private function describeError(?string $error): string
    {
        return match ($error) {
            'access_denied' => 'Login cancelado.',
            'client_not_configured', 'oauth_flow_failed' => 'Esse provedor de login está indisponível agora. Tente outro.',
            default => 'Não foi possível entrar. Tente novamente.',
        };
    }

    private function bounceToLogin(string $message): void
    {
        $this->replace('/login', ['error' => $message]);
    }
}
