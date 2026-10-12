<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A file:// URL for a bundled public/ asset.
 *
 * `asset()` resolves to `php://`, which native:image's loaders (AsyncImage,
 * Coil) don't understand — the image just renders blank. `file://` works
 * natively on both since PHP and the renderer share the device filesystem.
 */
final class LocalAsset
{
    public static function url(string $relativePath): string
    {
        $segments = array_map('rawurlencode', explode('/', public_path($relativePath)));

        return 'file://'.implode('/', $segments);
    }
}
