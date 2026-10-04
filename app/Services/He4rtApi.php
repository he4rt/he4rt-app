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
}
