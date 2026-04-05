<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaAsset
{
    /**
     * @var array<string, string>
     */
    private static array $publicImageUrlCache = [];

    /**
     * @var array<string, string|null>
     */
    private static array $publicImagePathCache = [];

    public static function publicImageStorageKey(string $relativePath): string
    {
        return 'images/'.self::normalizeRelativePath($relativePath);
    }

    public static function publicImageUrl(string $relativePath): string
    {
        $normalizedPath = self::normalizeRelativePath($relativePath);
        if ($normalizedPath === '') {
            return '';
        }

        if (array_key_exists($normalizedPath, self::$publicImageUrlCache)) {
            return self::$publicImageUrlCache[$normalizedPath];
        }

        $fallbackUrl = asset('images/'.$normalizedPath);
        $storageKey = self::publicImageStorageKey($normalizedPath);

        try {
            $s3Disk = Storage::disk('s3');

            if ($s3Disk->exists($storageKey)) {
                try {
                    $temporaryUrl = $s3Disk->temporaryUrl(
                        $storageKey,
                        now()->addMinutes((int) config('filesystems.media_url_ttl_minutes', 30))
                    );

                    return self::$publicImageUrlCache[$normalizedPath] = $temporaryUrl;
                } catch (\Throwable $e) {
                    // Fall through to non-signed URL.
                }

                try {
                    $publicUrl = $s3Disk->url($storageKey);

                    return self::$publicImageUrlCache[$normalizedPath] = $publicUrl;
                } catch (\Throwable $e) {
                    // Fall through to local asset fallback.
                }
            }
        } catch (\Throwable $e) {
            // Fall through to local asset fallback.
        }

        return self::$publicImageUrlCache[$normalizedPath] = $fallbackUrl;
    }

    public static function publicImageLocalPath(string $relativePath): ?string
    {
        $normalizedPath = self::normalizeRelativePath($relativePath);
        if ($normalizedPath === '') {
            return null;
        }

        if (array_key_exists($normalizedPath, self::$publicImagePathCache)) {
            return self::$publicImagePathCache[$normalizedPath];
        }

        $localPath = public_path('images/'.$normalizedPath);
        $storageKey = self::publicImageStorageKey($normalizedPath);

        try {
            $s3Disk = Storage::disk('s3');
            if ($s3Disk->exists($storageKey)) {
                $temporaryBasePath = storage_path('app/tmp/public_images');
                $temporaryPath = $temporaryBasePath.DIRECTORY_SEPARATOR.$normalizedPath;
                $temporaryDirectory = dirname($temporaryPath);

                if (! is_dir($temporaryDirectory)) {
                    mkdir($temporaryDirectory, 0755, true);
                }

                $contents = $s3Disk->get($storageKey);
                file_put_contents($temporaryPath, $contents);

                return self::$publicImagePathCache[$normalizedPath] = $temporaryPath;
            }
        } catch (\Throwable $e) {
            // Fall through to local fallback.
        }

        if (is_file($localPath)) {
            return self::$publicImagePathCache[$normalizedPath] = $localPath;
        }

        return self::$publicImagePathCache[$normalizedPath] = null;
    }

    private static function normalizeRelativePath(string $relativePath): string
    {
        return trim(str_replace('\\', '/', ltrim($relativePath, '/')), '/');
    }
}
