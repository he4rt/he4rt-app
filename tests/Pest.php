<?php

declare(strict_types=1);

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * @return array<string, mixed> One root timeline post, shaped like
 *                              TimelinePostResource. Timeline's `id` is a
 *                              UUID (the model uses HasUuids) — default to
 *                              a realistic one so tests catch int-vs-string
 *                              id bugs instead of masking them.
 */
function fakeTimelinePost(array $overrides = []): array
{
    return array_merge([
        'id' => '0199a1b2-3c4d-7e5f-8a6b-1234567890ab',
        'content' => 'Olá, comunidade He4rt!',
        'images' => [],
        'author' => [
            'id' => 10,
            'username' => 'danielhe4rt',
            'avatar_url' => 'https://x/avatar.png',
        ],
        'pinned' => false,
        'root_id' => null,
        'parent_id' => null,
        'replies_count' => 3,
        'likes_count' => 12,
        'liked_by_me' => false,
        'created_at' => now()->subHour()->toIso8601String(),
    ], $overrides);
}

/**
 * @return array<string, mixed> One event, shaped like EventResource.
 *                              Event's `id` is a UUID too, same reasoning
 *                              as fakeTimelinePost().
 */
function fakeEvent(array $overrides = []): array
{
    return array_merge([
        'id' => '0199b2c3-4d5e-7f60-9a7b-234567890abc',
        'slug' => 'workshop-laravel-na-pratica',
        'title' => 'Workshop: Laravel na prática',
        'description' => 'Mão na massa com Laravel.',
        'event_type' => 'workshop',
        'location' => 'Online',
        'starts_at' => now()->addDays(3)->toIso8601String(),
        'ends_at' => now()->addDays(3)->addHours(2)->toIso8601String(),
        'status' => 'published',
        'enrollment_status' => null,
    ], $overrides);
}

/**
 * @return array<string, mixed> One event, shaped like EventDetailResource
 *                              (GET /api/mobile/events/{event}) — adds the
 *                              enrollment policy/state fields that
 *                              EventResource (the list) doesn't carry.
 */
function fakeEventDetail(array $overrides = []): array
{
    $enrollment = array_key_exists('enrollment', $overrides) ? $overrides['enrollment'] : null;
    unset($overrides['enrollment']);

    return array_merge([
        'id' => '0199b2c3-4d5e-7f60-9a7b-234567890abc',
        'slug' => 'workshop-laravel-na-pratica',
        'title' => 'Workshop: Laravel na prática',
        'description' => 'Mão na massa com Laravel.',
        'event_type' => 'workshop',
        'location' => 'Online',
        'starts_at' => now()->addDays(3)->toIso8601String(),
        'ends_at' => now()->addDays(3)->addHours(2)->toIso8601String(),
        'status' => 'published',
        'enrollment_method' => 'rsvp',
        'capacity' => null,
        'has_waitlist' => false,
        'enrollment' => $enrollment,
        'can_apply' => false,
        'can_confirm_presence' => $enrollment === null,
        'is_event_full' => false,
    ], $overrides);
}

/**
 * @return array<string, mixed> Shaped like GET /api/mobile/me's response.
 */
function fakeMe(array $overrides = []): array
{
    return array_merge([
        'id' => '1',
        'username' => 'danielhe4rt',
        'avatar_url' => null,
        'is_super_admin' => false,
    ], $overrides);
}
