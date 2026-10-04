<?php

declare(strict_types=1);

namespace App\Support;

use Native\Mobile\Facades\SecureStorage;

/**
 * Persists the He4rt API access token in the device keychain/keystore.
 *
 * Single source of truth for "is the user logged in" across the app —
 * Splash, Perfil and the OAuth callback all go through this instead of
 * touching SecureStorage directly.
 */
final class AuthTokenStore
{
    private const TOKEN_KEY = 'he4rt_access_token';

    private const EXPIRES_AT_KEY = 'he4rt_access_token_expires_at';

    public function put(string $token, int $expiresInSeconds): void
    {
        SecureStorage::set(self::TOKEN_KEY, $token);
        SecureStorage::set(self::EXPIRES_AT_KEY, (string) now()->addSeconds($expiresInSeconds)->getTimestamp());
    }

    public function token(): ?string
    {
        return SecureStorage::get(self::TOKEN_KEY);
    }

    public function hasToken(): bool
    {
        return $this->token() !== null;
    }

    /**
     * Whether the locally-stored expiry has passed. Advisory only — the
     * server is the real source of truth, this just lets callers skip a
     * doomed request and refresh proactively.
     */
    public function isExpired(): bool
    {
        $expiresAt = SecureStorage::get(self::EXPIRES_AT_KEY);

        return $expiresAt === null || now()->getTimestamp() >= (int) $expiresAt;
    }

    public function clear(): void
    {
        SecureStorage::delete(self::TOKEN_KEY);
        SecureStorage::delete(self::EXPIRES_AT_KEY);
    }
}
