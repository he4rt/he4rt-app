<?php

declare(strict_types=1);

use App\NativeComponents\OAuthCallback;
use App\Support\AuthTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

it('has no nav title', function () {
    expect((new OAuthCallback)->navTitle())->toBe('');
});

it('exchanges the code, stores the token and goes home', function () {
    fakeSecureStorage();

    Http::fake([
        '*/api/mobile/auth/exchange' => Http::response([
            'access_token' => 'jwt-token',
            'token_type' => 'bearer',
            'expires_in' => 3_600,
        ]),
    ]);

    Native::visit('/oauth/callback?code=abc123')
        ->assertReplacedWith('/home');

    expect((new AuthTokenStore)->token())->toBe('jwt-token');
});

it('bounces back to login when the exchange fails', function () {
    Http::fake([
        '*/api/mobile/auth/exchange' => Http::response(['message' => 'invalid code'], 401),
    ]);

    Native::visit('/oauth/callback?code=invalid')
        ->assertReplacedWith('/login');
});

it('bounces back to login when the deep link carries no code', function () {
    Native::visit('/oauth/callback')
        ->assertReplacedWith('/login');
});

it('bounces back to login on the error path', function () {
    Native::visit('/oauth/error?error=access_denied')
        ->assertReplacedWith('/login');
});
