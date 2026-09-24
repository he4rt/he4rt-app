<?php

declare(strict_types=1);

use App\NativeComponents\Perfil;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

it('sets the nav title', function () {
    expect((new Perfil)->navTitle())->toBe('Perfil');
});

it('shows the username after a successful API call', function () {
    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt']),
    ]);

    Native::visit('/perfil')->assertSee('Conectado como danielhe4rt');
});

it('shows an error state when the API call fails', function () {
    Http::fake([
        '*/api/mobile/me' => Http::response(null, 401),
    ]);

    Native::visit('/perfil')->assertSee('Falha ao conectar com a API');
});
