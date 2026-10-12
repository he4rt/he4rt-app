<?php

declare(strict_types=1);

use App\NativeComponents\Timeline;
use App\Support\AuthTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

it('sets the nav title', function () {
    expect((new Timeline)->navTitle())->toBe('Timeline');
});

it('redirects to login when there is no stored token', function () {
    Native::visit('/timeline')->assertReplacedWith('/login');
});

it('shows the feed from the real API', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::response([
            'data' => [fakeTimelinePost(['content' => 'Olá, comunidade He4rt!'])],
        ]),
    ]);

    Native::visit('/timeline')
        ->assertSee('danielhe4rt')
        ->assertSee('Olá, comunidade He4rt!');
});

it('shows an empty state when the feed has no posts', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::response(['data' => []]),
    ]);

    Native::visit('/timeline')->assertSee('Ainda não tem nada na timeline');
});

it('shows an error state when the request fails', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::response(['message' => 'server error'], 500),
    ]);

    Native::visit('/timeline')->assertSee('Não foi possível carregar a timeline');
});

it('refreshes an expired token and retries the request', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::sequence()
            ->push(['message' => 'expired'], 401)
            ->push(['data' => [fakeTimelinePost()]]),
        '*/api/mobile/auth/refresh' => Http::response([
            'access_token' => 'new-token',
            'token_type' => 'bearer',
            'expires_in' => 3_600,
        ]),
    ]);

    Native::visit('/timeline')->assertSee('danielhe4rt');

    expect((new AuthTokenStore)->token())->toBe('new-token');
});

it('clears the token and redirects to login when the refresh also fails', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::response(['message' => 'expired'], 401),
        '*/api/mobile/auth/refresh' => Http::response(['message' => 'expired'], 401),
    ]);

    Native::visit('/timeline')->assertReplacedWith('/login');

    expect((new AuthTokenStore)->token())->toBeNull();
});

it('reloads the feed on pull-to-refresh', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::sequence()
            ->push(['data' => [fakeTimelinePost(['content' => 'Primeiro post'])]])
            ->push(['data' => [fakeTimelinePost(['content' => 'Post novo depois do refresh'])]]),
    ]);

    Native::visit('/timeline')
        ->assertSee('Primeiro post')
        ->call('refresh')
        ->assertSee('Post novo depois do refresh');
});
