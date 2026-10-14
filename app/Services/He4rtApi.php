<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for the heartdevs.com mobile API (JWT auth) — see
 * docs/plans/2026-09-22-api-mobile-jwt.md on that repo for the contract.
 */
final class He4rtApi
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.he4rt_api.base_url');
    }

    /**
     * URL to open via Browser::auth() to start the OAuth flow. $provider
     * is one of 'discord', 'github', 'twitch'.
     */
    public function oauthRedirectUrl(string $provider): string
    {
        return "{$this->baseUrl}/api/mobile/auth/{$provider}/redirect";
    }

    /**
     * Trade the one-time code from the OAuth deep link for a token pair.
     */
    public function exchange(string $code): Response
    {
        return Http::baseUrl($this->baseUrl)->post('/api/mobile/auth/exchange', ['code' => $code]);
    }

    /**
     * Renew the access token. The current token goes in the Authorization
     * header even if expired — the server accepts it within jwt.refresh_ttl.
     */
    public function refresh(string $token): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->post('/api/mobile/auth/refresh');
    }

    public function logout(string $token): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->post('/api/mobile/auth/logout');
    }

    public function me(string $token): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->get('/api/mobile/me');
    }

    /**
     * The authenticated user's professional profile — nickname, headline,
     * skills, work experience, availability. Auto-created empty on first
     * access (every field nullable/empty) if the user never filled it in.
     */
    public function profile(string $token): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->get('/api/mobile/profile');
    }

    /**
     * Root timeline posts, newest first. Pass the previous response's
     * `links.next` as $url to fetch the following page.
     */
    public function timeline(string $token, ?string $url = null): Response
    {
        if ($url !== null) {
            return Http::withToken($token)->get($url);
        }

        return Http::withToken($token)->baseUrl($this->baseUrl)->get('/api/mobile/timeline');
    }

    /**
     * Reply to a root post. Content validation (required, max 5000 chars)
     * happens server-side.
     */
    public function replyToPost(string $token, string $postId, string $content): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->post("/api/mobile/timeline/{$postId}/replies", [
            'content' => $content,
        ]);
    }

    /** Idempotent — liking an already-liked post has no effect. */
    public function likePost(string $token, string $postId): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->post("/api/mobile/timeline/{$postId}/like");
    }

    /** Idempotent — unliking a post that isn't liked has no effect. */
    public function unlikePost(string $token, string $postId): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->delete("/api/mobile/timeline/{$postId}/like");
    }

    /**
     * Events visible to participants (published, completed or cancelled),
     * closest first, with the current user's enrollment status on each.
     */
    public function events(string $token): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->get('/api/mobile/events');
    }

    /**
     * Full detail of one event — enrollment policy, the current user's
     * enrollment (if any), and the can_apply/can_confirm_presence/
     * is_event_full booleans the web panel itself uses to pick an action.
     */
    public function event(string $token, string $eventId): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->get("/api/mobile/events/{$eventId}");
    }

    /**
     * RSVP or apply, depending on the event's enrollment method — the
     * same action decides the resulting status (confirmed, waitlisted or
     * pending) server-side.
     */
    public function enrollInEvent(string $token, string $eventId): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->post("/api/mobile/events/{$eventId}/enroll");
    }

    /** Numeric code check-in — 4 or 6 digits, validated server-side. */
    public function checkIntoEvent(string $token, string $eventId, string $code): Response
    {
        return Http::withToken($token)->baseUrl($this->baseUrl)->post("/api/mobile/events/{$eventId}/check-in", [
            'code' => $code,
        ]);
    }
}
