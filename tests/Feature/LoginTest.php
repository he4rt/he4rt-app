<?php

declare(strict_types=1);

use App\NativeComponents\Login;
use Native\Mobile\Testing\Native;

it('has no nav title', function () {
    expect((new Login)->navTitle())->toBe('');
});

it('shows every OAuth provider', function () {
    Native::visit('/login')
        ->assertSee('Continuar com Discord')
        ->assertSee('Continuar com GitHub')
        ->assertSee('Continuar com Twitch');
});

it('shows an error message bounced back from the OAuth callback', function () {
    Native::visit('/login', ['error' => 'Login cancelado.'])
        ->assertSee('Login cancelado.');
});

it('opens the Discord OAuth redirect in an auth session', function () {
    Native::visit('/login')
        ->call('loginWithDiscord')
        ->assertNativeCalled('Browser.OpenAuth', fn (array $params) => $params['url'] === config('services.he4rt_api.base_url').'/api/mobile/auth/discord/redirect');
});

it('opens the GitHub OAuth redirect in an auth session', function () {
    Native::visit('/login')
        ->call('loginWithGitHub')
        ->assertNativeCalled('Browser.OpenAuth', fn (array $params) => $params['url'] === config('services.he4rt_api.base_url').'/api/mobile/auth/github/redirect');
});

it('opens the Twitch OAuth redirect in an auth session', function () {
    Native::visit('/login')
        ->call('loginWithTwitch')
        ->assertNativeCalled('Browser.OpenAuth', fn (array $params) => $params['url'] === config('services.he4rt_api.base_url').'/api/mobile/auth/twitch/redirect');
});
