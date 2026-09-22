<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

it('shows the username after a successful API call', function () {
    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt']),
    ]);

    Native::visit('/')->assertSee('Pong, danielhe4rt!');
});

it('shows an error state when the API call fails', function () {
    Http::fake([
        '*/api/mobile/me' => Http::response(null, 401),
    ]);

    Native::visit('/')->assertSee('Falha ao chamar a API');
});
