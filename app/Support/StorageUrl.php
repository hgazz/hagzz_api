<?php

namespace App\Support;

class StorageUrl
{
    public static function asset(?string $value, string $path, string $fallback): string
    {
        if (blank($value)) {
            return self::fallback($fallback);
        }

        $trimmed = trim($value);

        if (str_contains($trimmed, 'data:image')) {
            $pos = strpos($trimmed, 'data:image');
            return substr($trimmed, $pos);
        }

        if (str_starts_with($trimmed, 'data:') || str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://') || filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return str_contains($trimmed, 'bokit-app.s3.') ? self::fallback($fallback) : $trimmed;
        }

        // Files created by the current GCS uploader use PHP's uniqid format.
        // Older opaque names point to the retired S3 bucket and no longer exist.
        if (!preg_match('/^[a-f0-9]{13,14}\.\d+\.[A-Za-z0-9]+$/', $trimmed)) {
            return self::fallback($fallback);
        }

        return rtrim(config('services.storage.url'), '/')
            . '/' . trim($path, '/')
            . '/' . ltrim($trimmed, '/');
    }

    private static function fallback(string $path): string
    {
        return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}
