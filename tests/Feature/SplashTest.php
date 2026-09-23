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

it('starts the second LED half a lap behind the first', function () {
    $splash = new Splash;
    $splash->mount();

    expect($splash->ledBX)->not->toBe(0.0)
        ->and($splash->ledBY)->not->toBe(0.0)
        ->and([$splash->ledBX, $splash->ledBY])->not->toBe([$splash->ledAX, $splash->ledAY]);
});

it('advances both LEDs along their sampled path on every poll tick', function () {
    $native = Native::visit('/');
    $before = [$native->instance()->ledAX, $native->instance()->ledAY];

    $native->firePoll('advance');

    expect([$native->instance()->ledAX, $native->instance()->ledAY])->not->toBe($before);
});
