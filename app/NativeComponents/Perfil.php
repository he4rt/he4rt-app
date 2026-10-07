<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use App\Support\AuthTokenStore;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Perfil extends NativeComponent
{
    public string $status = 'loading';

    public ?string $username = null;

    public ?string $avatarUrl = null;

    public function mount(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token === null) {
            $this->replace('/login');

            return;
        }

        $this->loadProfile($token);
    }

    public function logout(): void
    {
        $token = (new AuthTokenStore)->token();

        if ($token !== null) {
            (new He4rtApi)->logout($token);
        }

        (new AuthTokenStore)->clear();
        $this->replace('/login');
    }

    public function navTitle(): string
    {
        return 'Perfil';
    }

    public function render(): View
    {
        return view('native.perfil');
    }

    private function loadProfile(string $token): void
    {
        $api = new He4rtApi;
        $response = $api->me($token);

        if ($response->unauthorized()) {
            $token = $this->refreshToken($api, $token);

            if ($token === null) {
                $this->replace('/login');

                return;
            }

            $response = $api->me($token);
        }

        if (!$response->successful()) {
            $this->status = 'error';

            return;
        }

        $this->status = 'ok';
        $this->username = $response->json('username');
        $this->avatarUrl = $response->json('avatar_url');
    }

    /** Null means the token could not be refreshed — caller should log out. */
    private function refreshToken(He4rtApi $api, string $expiredToken): ?string
    {
        $response = $api->refresh($expiredToken);

        if (!$response->successful()) {
            (new AuthTokenStore)->clear();

            return null;
        }

        $accessToken = (string) $response->json('access_token');
        (new AuthTokenStore)->put($accessToken, (int) $response->json('expires_in'));

        return $accessToken;
    }
}
