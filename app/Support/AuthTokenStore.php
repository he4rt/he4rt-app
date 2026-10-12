<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuthToken;
use Throwable;

/**
 * Single source of truth for "is the user logged in" — Splash, Perfil and
 * OAuthCallback go through this instead of touching storage directly.
 *
 * Backed by local SQLite (`auth_tokens` table), not the OS keychain —
 * SecureStorage needs the paid nativephp/mobile-secure-storage plugin,
 * which isn't installed. Revisit before shipping anything that needs
 * hardware-backed storage.
 */
final class AuthTokenStore
{
    private const TOKEN_KEY = 'he4rt_access_token';

    private const EXPIRES_AT_KEY = 'he4rt_access_token_expires_at';

    /** @return bool Whether both writes actually succeeded. */
    public function put(string $token, int $expiresInSeconds): bool
    {
        try {
            $this->set(self::TOKEN_KEY, $token);
            $this->set(self::EXPIRES_AT_KEY, (string) now()->addSeconds($expiresInSeconds)->getTimestamp());

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function token(): ?string
    {
        return $this->get(self::TOKEN_KEY);
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
        $expiresAt = $this->get(self::EXPIRES_AT_KEY);

        return $expiresAt === null || now()->getTimestamp() >= (int) $expiresAt;
    }

    public function clear(): void
    {
        AuthToken::destroy([self::TOKEN_KEY, self::EXPIRES_AT_KEY]);
    }

    private function get(string $key): ?string
    {
        return AuthToken::find($key)?->value;
    }

    private function set(string $key, string $value): void
    {
        AuthToken::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
