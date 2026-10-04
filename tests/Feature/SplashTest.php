<?php

declare(strict_types=1);

use App\NativeComponents\Splash;
use Native\Mobile\Testing\Native;

it('has no nav title', function () {
    expect((new Splash)->navTitle())->toBe('');
});

it('shows the wordmark', function () {
    Native::visit('/')->assertSee('He4rt Devs');
});

it('replaces itself with home when a token is already stored', function () {
    $bridge = fakeSecureStorage();
    $bridge->respondTo('SecureStorage.Get', fn (array $params) => $params['key'] === 'he4rt_access_token'
        ? ['value' => 'stored-token']
        : ['value' => null]);

    Native::visit('/')
        ->firePoll('finish')
        ->assertReplacedWith('/home');
});

it('replaces itself with login when there is no stored token', function () {
    Native::visit('/')
        ->firePoll('finish')
        ->assertReplacedWith('/login');
});

it('serves the animated logo html the webview embeds', function () {
    $this->get('/web/splash-animation')
        ->assertOk()
        ->assertSee('he4rt-led-run', false)
        ->assertSee('class="led"', false)
        ->assertSee('class="led b"', false);
});
