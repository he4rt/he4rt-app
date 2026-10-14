<?php

declare(strict_types=1);

use App\NativeComponents\Eventos;
use App\Support\AuthTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

it('sets the nav title', function () {
    expect((new Eventos)->navTitle())->toBe('Eventos');
});

it('redirects to login when there is no stored token', function () {
    Native::visit('/eventos')->assertReplacedWith('/login');
});

it('splits events into upcoming and past sections', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => [
            fakeEvent(['title' => 'Workshop futuro', 'status' => 'published', 'starts_at' => now()->addDays(3)->toIso8601String()]),
            fakeEvent(['title' => 'Meetup passado', 'status' => 'completed', 'starts_at' => now()->subDays(10)->toIso8601String()]),
        ]]),
    ]);

    Native::visit('/eventos')
        ->assertSee('Próximos')
        ->assertSee('Workshop futuro')
        ->assertSee('Anteriores')
        ->assertSee('Meetup passado');
});

it('shows the real enrollment status instead of a generic pill', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['data' => [
            fakeEvent(['enrollment_status' => 'waitlisted']),
        ]]),
    ]);

    Native::visit('/eventos')->assertSee('Lista de espera');
});

it('shows an empty state when there are no events at all', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake(['*/api/mobile/events' => Http::response(['data' => []])]);

    Native::visit('/eventos')->assertSee('Nenhum evento no momento.');
});

it('shows an error state when the request fails', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake(['*/api/mobile/events' => Http::response(['message' => 'server error'], 500)]);

    Native::visit('/eventos')->assertSee('Não foi possível carregar os eventos.');
});

it('reloads the list on pull-to-refresh', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::sequence()
            ->push(['data' => [fakeEvent(['title' => 'Primeiro evento'])]])
            ->push(['data' => [fakeEvent(['title' => 'Evento depois do refresh'])]]),
    ]);

    Native::visit('/eventos')
        ->assertSee('Primeiro evento')
        ->call('refresh')
        ->assertSee('Evento depois do refresh');
});

it('navigates to the event detail screen', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    $eventId = '0199b2c3-4d5e-7f60-9a7b-234567890abc';

    Http::fake(['*/api/mobile/events' => Http::response(['data' => [fakeEvent(['id' => $eventId])]])]);

    Native::visit('/eventos')
        ->press("goToEvent('{$eventId}')")
        ->assertNavigatedTo("/eventos/{$eventId}");
});

it('bounces to login when refreshing an expired token fails', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/events' => Http::response(['message' => 'expired'], 401),
        '*/api/mobile/auth/refresh' => Http::response(['message' => 'expired'], 401),
    ]);

    Native::visit('/eventos')->assertReplacedWith('/login');

    expect((new AuthTokenStore)->token())->toBeNull();
});
