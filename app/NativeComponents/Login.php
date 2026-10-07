<?php

declare(strict_types=1);

namespace App\NativeComponents;

use App\Services\He4rtApi;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Browser;

class Login extends NativeComponent
{
    /**
     * Mensagem de erro vinda de volta do /oauth/{action} quando o login
     * falha — ver OAuthCallback::bounceToLogin().
     */
    public ?string $error = null;

    public function mount(): void
    {
        $this->error = $this->data('error');
    }

    public function loginWithDiscord(): void
    {
        $this->startOAuth('discord');
    }

    public function loginWithGitHub(): void
    {
        $this->startOAuth('github');
    }

    public function loginWithTwitch(): void
    {
        $this->startOAuth('twitch');
    }

    public function navTitle(): string
    {
        return '';
    }

    public function render(): View
    {
        return view('native.login');
    }

    private function startOAuth(string $provider): void
    {
        $this->error = null;

        Browser::auth((new He4rtApi)->oauthRedirectUrl($provider));
    }
}
