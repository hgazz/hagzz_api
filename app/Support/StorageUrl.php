<?php

namespace App\Support;

class StorageUrl
{
    public static function asset(?string $value, string $path, string $fallback): string
    {
        if (blank($value)) {
            return self::fallback($fallback);
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return str_contains($value, 'bokit-app.s3.') ? self::fallback($fallback) : $value;
        }

        // Files created by the current GCS uploader use PHP's uniqid format.
        // Older opaque names point to the retired S3 bucket and no longer exist.
        if (!preg_match('/^[a-f0-9]{13,14}\.\d+\.[A-Za-z0-9]+$/', $value)) {
            return self::fallback($fallback);
        }

        return rtrim(config('services.storage.url'), '/')
            . '/' . trim($path, '/')
            . '/' . ltrim($value, '/');
    }

    private static function fallback(string $path): string
    {
        return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}
