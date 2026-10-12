<?php

declare(strict_types=1);

use App\NativeComponents\Perfil;
use App\Support\AuthTokenStore;
use Illuminate\Support\Facades\Http;
use Native\Mobile\Testing\Native;

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

it('shows the full profile when it has data', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt', 'avatar_url' => null]),
        '*/api/mobile/profile' => Http::response([
            'data' => [
                'nickname' => 'daniel',
                'headline' => 'Backend Engineer',
                'about' => 'Apaixonado por Laravel e café.',
                'seniority_level' => 'senior',
                'years_experience' => 5,
                'social_links' => ['instagram' => 'danielhe4rt', 'website' => 'https://daniel.dev'],
                'available_for_proposals' => true,
                'start_availability' => '2_weeks',
                'expected_salary_min' => '8000.00',
                'expected_salary_max' => '12000.00',
                'preferences' => ['has_disability' => false, 'willing_to_relocate' => false, 'is_open_to_remote' => true, 'employment_types' => ['pj']],
                'skills' => [
                    ['id' => 1, 'name' => 'PHP', 'category' => 'language', 'proficiency' => 'advanced', 'years_experience' => 5],
                ],
                'work_experiences' => [
                    [
                        'id' => 1,
                        'company_name' => 'He4rt Devs',
                        'position' => 'Backend Engineer',
                        'description' => 'Cuidando da API mobile.',
                        'start_date' => '2023-01-01',
                        'end_date' => null,
                        'is_currently_working_here' => true,
                    ],
                ],
            ],
        ]),
    ]);

    Native::visit('/perfil')
        ->assertSee('Backend Engineer')
        ->assertSee('Sênior')
        ->assertSee('5 anos de experiência')
        ->assertSee('Aberto a propostas')
        ->assertSee('2 semanas')
        ->assertSee('Apaixonado por Laravel e café.')
        ->assertSee('PHP · Avançado')
        ->assertSee('He4rt Devs')
        ->assertSee('jan/2023 — atual')
        ->assertSee('Instagram')
        ->assertSee('Website');
});

it('still shows identity when the profile fetch fails', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt', 'avatar_url' => null]),
        '*/api/mobile/profile' => Http::response(['message' => 'server error'], 500),
    ]);

    Native::visit('/perfil')
        ->assertSee('danielhe4rt')
        ->assertSee('Sair');
});

it('skips optional sections for a freshly auto-created empty profile', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt', 'avatar_url' => null]),
        '*/api/mobile/profile' => Http::response([
            'data' => [
                'nickname' => null,
                'headline' => null,
                'about' => null,
                'seniority_level' => null,
                'years_experience' => null,
                'social_links' => [],
                'available_for_proposals' => false,
                'start_availability' => null,
                'expected_salary_min' => null,
                'expected_salary_max' => null,
                'preferences' => ['has_disability' => false, 'willing_to_relocate' => false, 'is_open_to_remote' => false, 'employment_types' => []],
                'skills' => [],
                'work_experiences' => [],
            ],
        ]),
    ]);

    Native::visit('/perfil')
        ->assertSee('Membro da comunidade He4rt')
        ->assertDontSee('Aberto a propostas')
        ->assertDontSee('Sobre')
        ->assertDontSee('Skills')
        ->assertDontSee('Experiência')
        ->assertDontSee('Links');
});

it('opens a social link in the browser', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/me' => Http::response(['id' => '1', 'username' => 'danielhe4rt', 'avatar_url' => null]),
        '*/api/mobile/profile' => Http::response([
            'data' => [
                'social_links' => ['instagram' => 'danielhe4rt'],
                'skills' => [],
                'work_experiences' => [],
                'preferences' => [],
            ],
        ]),
    ]);

    Native::visit('/perfil')
        ->press("openSocialLink('https://instagram.com/danielhe4rt')")
        ->assertNativeCalled('Browser.Open', fn (array $params) => $params['url'] === 'https://instagram.com/danielhe4rt');
});
