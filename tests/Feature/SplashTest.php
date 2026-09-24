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

it('replaces itself with home once the poll fires', function () {
    Native::visit('/')
        ->firePoll('finish')
        ->assertReplacedWith('/home');
});

it('serves the animated logo html the webview embeds', function () {
    $this->get('/web/splash-animation')
        ->assertOk()
        ->assertSee('he4rt-led-run', false)
        ->assertSee('class="led"', false)
        ->assertSee('class="led b"', false);
});
