<?php

declare(strict_types=1);

use App\Support\AuthTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

const EVENTO_ID = '0199b2c3-4d5e-7f60-9a7b-234567890abc';

it('redirects to login when there is no stored token', function () {
    Native::visit('/eventos/'.EVENTO_ID)->assertReplacedWith('/login');
});

it('shows the event detail when there is no enrollment yet', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response([
            'data' => fakeEventDetail(['title' => 'Workshop: Laravel na prática', 'location' => 'Online']),
        ]),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    $component = Native::visit('/eventos/'.EVENTO_ID)
        ->assertSee('Workshop: Laravel na prática')
        ->assertSee('Online')
        ->assertSee('Confirmar presença');

    expect($component->get('title'))->toBe('Workshop: Laravel na prática');
});

it('shows the real enrollment status when already enrolled', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response([
            'data' => fakeEventDetail([
                'can_confirm_presence' => false,
                'enrollment' => ['status' => 'confirmed', 'waitlist_position' => null, 'enrolled_at' => now()->toIso8601String(), 'confirmed_at' => now()->toIso8601String(), 'checked_in_at' => null],
            ]),
        ]),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->assertSee('Confirmado')
        ->assertSee('Fazer check-in')
        ->assertDontSee('Confirmar presença');
});

it('shows a waiting message for application-based events', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response([
            'data' => fakeEventDetail(['enrollment_method' => 'application', 'can_apply' => true, 'can_confirm_presence' => false]),
        ]),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->assertSee('inscrição por formulário')
        ->assertDontSee('Confirmar presença');
});

it('shows a full-event message instead of the confirm button', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response([
            'data' => fakeEventDetail(['is_event_full' => true, 'can_confirm_presence' => false]),
        ]),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->assertSee('Evento lotado')
        ->assertDontSee('Confirmar presença');
});

it('confirms presence and refreshes the enrollment state', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::sequence()
            ->push(['data' => fakeEventDetail()])
            ->push(['data' => fakeEventDetail([
                'can_confirm_presence' => false,
                'enrollment' => ['status' => 'confirmed', 'waitlist_position' => null, 'enrolled_at' => now()->toIso8601String(), 'confirmed_at' => now()->toIso8601String(), 'checked_in_at' => null],
            ])]),
        '*/api/mobile/events/'.EVENTO_ID.'/enroll' => Http::response(['status' => 'confirmed', 'waitlist_position' => null], 201),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->assertSee('Confirmar presença')
        ->call('enroll')
        ->assertSee('Confirmado');
});

it('shows the server error message when enrolling fails', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response(['data' => fakeEventDetail()]),
        '*/api/mobile/events/'.EVENTO_ID.'/enroll' => Http::response(['message' => 'Este evento já aconteceu.'], 422),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->call('enroll')
        ->assertSee('Este evento já aconteceu.');
});

it('opens and cancels the check-in composer', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response([
            'data' => fakeEventDetail([
                'can_confirm_presence' => false,
                'enrollment' => ['status' => 'confirmed', 'waitlist_position' => null, 'enrolled_at' => null, 'confirmed_at' => null, 'checked_in_at' => null],
            ]),
        ]),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->press('startCheckIn')
        ->assertSet('showCheckIn', true)
        ->call('cancelCheckIn')
        ->assertSet('showCheckIn', false)
        ->assertSet('checkInCode', '');
});

it('rejects a malformed check-in code before calling the API', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response([
            'data' => fakeEventDetail([
                'can_confirm_presence' => false,
                'enrollment' => ['status' => 'confirmed', 'waitlist_position' => null, 'enrolled_at' => null, 'confirmed_at' => null, 'checked_in_at' => null],
            ]),
        ]),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->set('checkInCode', '12')
        ->call('submitCheckIn')
        ->assertSee('O código tem 4 ou 6 dígitos.');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'check-in'));
});

it('checks in with a valid code and closes the composer', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::sequence()
            ->push(['data' => fakeEventDetail([
                'can_confirm_presence' => false,
                'enrollment' => ['status' => 'confirmed', 'waitlist_position' => null, 'enrolled_at' => null, 'confirmed_at' => null, 'checked_in_at' => null],
            ])])
            ->push(['data' => fakeEventDetail([
                'can_confirm_presence' => false,
                'enrollment' => ['status' => 'checked_in', 'waitlist_position' => null, 'enrolled_at' => null, 'confirmed_at' => null, 'checked_in_at' => now()->toIso8601String()],
            ])]),
        '*/api/mobile/events/'.EVENTO_ID.'/check-in' => Http::response(['event_date' => now()->toDateString(), 'checked_in_at' => now()->toIso8601String()], 201),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->set('checkInCode', '1234')
        ->call('submitCheckIn')
        ->assertSet('showCheckIn', false)
        ->assertSee('Check-in realizado');
});

it('shows the server error message when the check-in code is wrong', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response([
            'data' => fakeEventDetail([
                'can_confirm_presence' => false,
                'enrollment' => ['status' => 'confirmed', 'waitlist_position' => null, 'enrolled_at' => null, 'confirmed_at' => null, 'checked_in_at' => null],
            ]),
        ]),
        '*/api/mobile/events/'.EVENTO_ID.'/check-in' => Http::response(['message' => 'Código inválido ou expirado.'], 422),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->call('startCheckIn')
        ->set('checkInCode', '1234')
        ->call('submitCheckIn')
        ->assertSet('showCheckIn', true)
        ->assertSee('Código inválido ou expirado.');
});

it('bounces to login when refreshing an expired token fails', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response(['message' => 'expired'], 401),
        '*/api/mobile/auth/refresh' => Http::response(['message' => 'expired'], 401),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)->assertReplacedWith('/login');

    expect((new AuthTokenStore)->token())->toBeNull();
});

it('shows the staff area and generates a check-in code for a super admin', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response(['data' => fakeEventDetail()]),
        '*/api/mobile/me' => Http::response(fakeMe(['is_super_admin' => true])),
        '*/api/mobile/events/'.EVENTO_ID.'/check-in-codes' => Http::response([
            'code' => '123456',
            'event_date' => now()->toDateString(),
            'expires_at' => now()->addHours(2)->toIso8601String(),
        ], 201),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->assertSee('Área da staff')
        ->press('generateCheckInCode')
        ->assertSee('123456');
});

it('hides the staff area for a regular user', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response(['data' => fakeEventDetail()]),
        '*/api/mobile/me' => Http::response(fakeMe()),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)->assertDontSee('Área da staff');
});

it('shows an error when generating the check-in code fails', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response(['data' => fakeEventDetail()]),
        '*/api/mobile/me' => Http::response(fakeMe(['is_super_admin' => true])),
        '*/api/mobile/events/'.EVENTO_ID.'/check-in-codes' => Http::response(['message' => 'forbidden'], 403),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->press('generateCheckInCode')
        ->assertSee('Não foi possível gerar o código. Tente novamente.');
});

it('generates a 4-digit code when that digit count is selected', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/events/'.EVENTO_ID => Http::response(['data' => fakeEventDetail()]),
        '*/api/mobile/me' => Http::response(fakeMe(['is_super_admin' => true])),
        '*/api/mobile/events/'.EVENTO_ID.'/check-in-codes' => Http::response([
            'code' => '1234',
            'event_date' => now()->toDateString(),
            'expires_at' => now()->addHours(2)->toIso8601String(),
        ], 201),
    ]);

    Native::visit('/eventos/'.EVENTO_ID)
        ->press('setCodeDigits(4)')
        ->press('generateCheckInCode')
        ->assertSee('1234');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'check-in-codes') && $request['digits'] === 4);
});
