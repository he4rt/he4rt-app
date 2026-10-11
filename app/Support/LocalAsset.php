<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A file:// URL for a bundled public/ asset.
 *
 * The native:image element loads its `src` with plain AsyncImage (iOS) /
 * Coil (Android) — neither understands the `php://` scheme NativePHP
 * rewrites APP_URL to on device (that scheme only exists inside a WKWebView
 * via PHPSchemeHandler), so `asset()` resolves to a URL nothing can fetch
 * and the image silently renders blank. `file://` is natively supported by
 * both loaders with no extra wiring, since the embedded PHP runtime and the
 * native renderer share the same on-device filesystem.
 */
final class LocalAsset
{
    public static function url(string $relativePath): string
    {
        $segments = array_map('rawurlencode', explode('/', public_path($relativePath)));

        return 'file://'.implode('/', $segments);
    }
}
