<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

/**
 * Lands here when the OS opens `{scheme}://oauth/{action}` — the deep link
 * heartdevs.com's web OAuth callback redirects to.
 *
 * `code`/`error` arrive as query string, which NativePHP doesn't surface
 * as route params — parsed off the raw current URI instead.
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

        $store = new AuthTokenStore;
        $stored = $store->put(
            (string) $response->json('access_token'),
            (int) $response->json('expires_in'),
        );

        // Verify the write landed before navigating — a silent failure
        // here would otherwise surface as an inexplicable bounce to
        // Login from Perfil, two screens later.
        if (!$stored || $store->token() === null) {
            $this->bounceToLogin('Não foi possível salvar sua sessão. Tente novamente.');

            return;
        }

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
