<?php

declare(strict_types=1);

namespace App\Support;

use App\Services\He4rtApi;
use Closure;
use Illuminate\Http\Client\Response;

/**
 * Runs an authenticated He4rtApi call, retrying once with a refreshed
 * token on a 401. Shared by every NativeComponent that calls a protected
 * endpoint (Perfil, Timeline) — duplicating this by hand a third time is
 * what finally made it worth extracting.
 */
trait RefreshesExpiredToken
{
    /**
     * Runs $makeRequest with the given token, retrying once with a
     * refreshed token if the first attempt is unauthorized. Null means
     * the session is gone (refresh also failed) — caller should bounce
     * to Login.
     */
    private function withTokenRetry(string $token, Closure $makeRequest): ?Response
    {
        $response = $makeRequest($token);

        if (!$response->unauthorized()) {
            return $response;
        }

        $refreshedToken = $this->refreshToken(new He4rtApi, $token);

        if ($refreshedToken === null) {
            return null;
        }

        return $makeRequest($refreshedToken);
    }

    /** Null means the token could not be refreshed — caller should log out. */
    private function refreshToken(He4rtApi $api, string $expiredToken): ?string
    {
        $response = $api->refresh($expiredToken);

        if (!$response->successful()) {
            (new AuthTokenStore)->clear();

            return null;
        }

        $accessToken = (string) $response->json('access_token');
        (new AuthTokenStore)->put($accessToken, (int) $response->json('expires_in'));

        return $accessToken;
    }
}
