<?php

declare(strict_types=1);

use App\NativeComponents\Home;
use Native\Mobile\Testing\Native;

it('sets the nav title', function () {
    expect((new Home)->navTitle())->toBe('Início');
});

it('shows the community stats and mocked sections', function () {
    Native::visit('/home')
        ->assertSee('Comunidade He4rt')
        ->assertSee('Próximos eventos')
        ->assertSee('Workshop: Laravel na prática')
        ->assertSee('Da comunidade');
});

it('replaces itself with eventos when going to the events teaser', function () {
    Native::visit('/home')
        ->call('goToEventos')
        ->assertReplacedWith('/eventos');
});

it('replaces itself with timeline when going to the community teaser', function () {
    Native::visit('/home')
        ->call('goToTimeline')
        ->assertReplacedWith('/timeline');
});

it('opens the He4rt Discord invite', function () {
    Native::visit('/home')
        ->call('goToDiscord')
        ->assertNativeCalled('Browser.Open', fn (array $params) => $params['url'] === 'https://discord.com/invite/he4rt');
});
