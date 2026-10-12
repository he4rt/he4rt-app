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

it('shows a load more affordance when there is a next page', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::response([
            'data' => [fakeTimelinePost()],
            'links' => ['next' => 'http://127.0.0.1:8000/api/mobile/timeline?page=2'],
        ]),
    ]);

    Native::visit('/timeline')->assertSee('Carregar mais');
});

it('hides the load more affordance when there is no next page', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::response([
            'data' => [fakeTimelinePost()],
            'links' => ['next' => null],
        ]),
    ]);

    Native::visit('/timeline')->assertDontSee('Carregar mais');
});

it('loads the next page and appends it to the feed', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline*' => Http::sequence()
            ->push([
                'data' => [fakeTimelinePost(['id' => '0199a1b2-0000-7e5f-8a6b-1234567890ab', 'content' => 'Primeira página'])],
                'links' => ['next' => 'http://127.0.0.1:8000/api/mobile/timeline?page=2'],
            ])
            ->push([
                'data' => [fakeTimelinePost(['id' => '0199a1b2-1111-7e5f-8a6b-1234567890ab', 'content' => 'Segunda página'])],
                'links' => ['next' => null],
            ]),
    ]);

    Native::visit('/timeline')
        ->assertSee('Primeira página')
        ->call('loadMore')
        ->assertSee('Primeira página')
        ->assertSee('Segunda página')
        ->assertDontSee('Carregar mais');
});

it('bounces to login when loading more fails to refresh an expired token', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    Http::fake([
        '*/api/mobile/timeline' => Http::response([
            'data' => [fakeTimelinePost()],
            'links' => ['next' => 'http://127.0.0.1:8000/api/mobile/timeline?page=2'],
        ]),
    ]);

    $component = Native::visit('/timeline')->assertSee('Carregar mais');

    Http::fake([
        '*/api/mobile/timeline*' => Http::response(['message' => 'expired'], 401),
        '*/api/mobile/auth/refresh' => Http::response(['message' => 'expired'], 401),
    ]);

    $component->call('loadMore')->assertReplacedWith('/login');

    expect((new AuthTokenStore)->token())->toBeNull();
});

it('opens and closes the reply composer', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    $postId = '0199a1b2-3c4d-7e5f-8a6b-1234567890ab';

    Http::fake([
        '*/api/mobile/timeline' => Http::response(['data' => [fakeTimelinePost(['id' => $postId])]]),
    ]);

    $component = Native::visit('/timeline')
        ->press("startReply('{$postId}')")
        ->assertSet('replyingToPostId', $postId);

    $component->call('cancelReply')
        ->assertSet('replyingToPostId', null)
        ->assertSet('replyContent', '');
});

it('submits a reply and bumps the visible replies count', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    $postId = '0199a1b2-3c4d-7e5f-8a6b-1234567890ab';

    Http::fake([
        '*/api/mobile/timeline' => Http::response([
            'data' => [fakeTimelinePost(['id' => $postId, 'replies_count' => 3])],
        ]),
        "*/api/mobile/timeline/{$postId}/replies" => Http::response(['data' => ['id' => 'reply-uuid']], 201),
    ]);

    Native::visit('/timeline')
        ->press("startReply('{$postId}')")
        ->set('replyContent', 'Concordo!')
        ->call('submitReply')
        ->assertSet('replyingToPostId', null)
        ->assertSee('4');
});

it('shows a validation error without closing the composer', function () {
    (new AuthTokenStore)->put('valid-token', 3_600);

    $postId = '0199a1b2-3c4d-7e5f-8a6b-1234567890ab';

    Http::fake([
        '*/api/mobile/timeline' => Http::response(['data' => [fakeTimelinePost(['id' => $postId])]]),
        "*/api/mobile/timeline/{$postId}/replies" => Http::response([
            'errors' => ['content' => ['O campo content é obrigatório.']],
        ], 422),
    ]);

    Native::visit('/timeline')
        ->press("startReply('{$postId}')")
        ->set('replyContent', 'x')
        ->call('submitReply')
        ->assertSet('replyingToPostId', $postId)
        ->assertSee('O campo content é obrigatório.');
});

it('refreshes an expired token and retries the reply', function () {
    (new AuthTokenStore)->put('expired-token', 3_600);

    $postId = '0199a1b2-3c4d-7e5f-8a6b-1234567890ab';

    Http::fake([
        '*/api/mobile/timeline' => Http::response(['data' => [fakeTimelinePost(['id' => $postId])]]),
        "*/api/mobile/timeline/{$postId}/replies" => Http::sequence()
            ->push(['message' => 'expired'], 401)
            ->push(['data' => ['id' => 'reply-uuid']], 201),
        '*/api/mobile/auth/refresh' => Http::response([
            'access_token' => 'new-token',
            'token_type' => 'bearer',
            'expires_in' => 3_600,
        ]),
    ]);

    Native::visit('/timeline')
        ->press("startReply('{$postId}')")
        ->set('replyContent', 'Concordo!')
        ->call('submitReply')
        ->assertSet('replyingToPostId', null);

    expect((new AuthTokenStore)->token())->toBe('new-token');
});
