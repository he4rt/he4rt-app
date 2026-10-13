<?php

declare(strict_types=1);

use App\NativeComponents\Home;
use App\Support\AuthTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

it('sets the nav title', function () {
    expect((new Home)->navTitle())->toBe('Início');
});

it('redirects to login when there is no stored token', function () {
    Native::visit('/home')->assertReplacedWith('/login');
});

it('shows upcoming published events and the timeline teaser', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => [fakeEvent(['title' => 'Workshop: Laravel na prática'])]]),
        '*/api/mobile/timeline' => Http::response(['data' => [
            fakeTimelinePost(['content' => 'Primeiro post da comunidade']),
        ]]),
    ]);

    Native::visit('/home')
        ->assertSee('Comunidade He4rt')
        ->assertSee('Workshop: Laravel na prática')
        ->assertSee('Em breve')
        ->assertSee('Primeiro post da comunidade')
        ->assertSee('Da comunidade');
});

it('shows the enrollment status badge instead of "Em breve" when enrolled', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => [fakeEvent(['enrollment_status' => 'confirmed'])]]),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')
        ->assertSee('Inscrito')
        ->assertDontSee('Em breve');
});

it('filters out events that already happened or are not published', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => [
            fakeEvent(['title' => 'Evento futuro publicado']),
            fakeEvent(['title' => 'Evento passado', 'starts_at' => now()->subDay()->toIso8601String()]),
            fakeEvent(['title' => 'Evento cancelado', 'status' => 'cancelled']),
        ]]),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')
        ->assertSee('Evento futuro publicado')
        ->assertDontSee('Evento passado')
        ->assertDontSee('Evento cancelado');
});

it('shows an empty state when there are no upcoming events', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => []]),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')->assertSee('Nenhum evento agendado no momento.');
});

it('shows an error state when events fail to load', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['message' => 'server error'], 500),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')->assertSee('Não foi possível carregar os próximos eventos.');
});

it('shows an empty state when the timeline has no posts', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => []]),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')->assertSee('Ainda não tem nada na timeline.');
});

it('shows an error state when the timeline fails to load', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => []]),
        '*/api/mobile/timeline' => Http::response(['message' => 'server error'], 500),
    ]);

    Native::visit('/home')->assertSee('Não foi possível carregar a timeline.');
});

it('replaces itself with eventos when going to the events teaser', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => []]),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')
        ->call('goToEventos')
        ->assertReplacedWith('/eventos');
});

it('replaces itself with timeline when going to the community teaser', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => []]),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')
        ->call('goToTimeline')
        ->assertReplacedWith('/timeline');
});

it('opens the He4rt Discord invite', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => []]),
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/home')
        ->call('goToDiscord')
        ->assertNativeCalled('Browser.Open', fn (array $params) => $params['url'] === 'https://discord.com/invite/he4rt');
});

it('bounces to login when refreshing an expired token fails for events', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['message' => 'expired'], 401),
        '*/api/mobile/auth/refresh' => Http::response(['message' => 'expired'], 401),
    ]);

    Native::visit('/home')->assertReplacedWith('/login');

    expect((new AuthTokenStore)->token())->toBeNull();
});
