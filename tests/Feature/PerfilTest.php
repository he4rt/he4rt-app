<?php

declare(strict_types=1);

use App\NativeComponents\Perfil;
use App\Support\AuthTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    fakeSecureStorage();
});

it('sets the nav title', function () {
    expect((new Perfil)->navTitle())->toBe('Perfil');
});

it('redirects to login when there is no stored token', function () {
    Native::visit('/perfil')->assertReplacedWith('/login');
});

it('shows the authenticated user profile', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt', 'avatar_url' => 'https://x/a.png']),
    ]);

    Native::visit('/perfil')->assertSee('danielhe4rt');
});

it('refreshes an expired token and retries the request', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::sequence()
            ->push(['message' => 'expired'], 401)
            ->push(['id' => '1', 'username' => 'danielhe4rt', 'avatar_url' => null]),
        '*/api/mobile/auth/refresh' => Http::response([
            'access_token' => 'new-token',
            'token_type' => 'bearer',
            'expires_in' => 3_600,
        ]),
    ]);

    Native::visit('/perfil')->assertSee('danielhe4rt');

    expect((new AuthTokenStore)->token())->toBe('new-token');
});

it('clears the token and redirects to login when the refresh also fails', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::response(['message' => 'expired'], 401),
        '*/api/mobile/auth/refresh' => Http::response(['message' => 'expired'], 401),
    ]);

    Native::visit('/perfil')->assertReplacedWith('/login');

    expect((new AuthTokenStore)->token())->toBeNull();
});

it('logs out, clears the token and redirects to login', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt', 'avatar_url' => null]),
        '*/api/mobile/auth/logout' => Http::response(status: 204),
    ]);

    Native::visit('/perfil')
        ->call('logout')
        ->assertReplacedWith('/login');

    expect((new AuthTokenStore)->token())->toBeNull();
});
