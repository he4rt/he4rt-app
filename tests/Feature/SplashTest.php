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
