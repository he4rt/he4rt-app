<?php

declare(strict_types=1);

use App\NativeComponents\Timeline;
use Native\Mobile\Testing\Native;

it('sets the nav title', function () {
    expect((new Timeline)->navTitle())->toBe('Timeline');
});

it('shows every mocked post by default', function () {
    Native::visit('/timeline')
        ->assertSee('Refatoramos o pool de conexões do Postgres')
        ->assertSee('turma de mentoria de Setembro')
        ->assertSee('He4rt Conf 2025');
});

it('narrows the feed when a category filter is selected', function () {
    Native::visit('/timeline')
        ->call('setFilter', 'mentorias')
        ->assertSee('turma de mentoria de Setembro')
        ->assertDontSee('He4rt Conf 2025');
});

it('shows an empty state when a filter has no posts', function () {
    Native::visit('/timeline')
        ->call('setFilter', 'inexistente')
        ->assertSee('Nada por aqui ainda nessa categoria');
});
