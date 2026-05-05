<?php

namespace App\Http\Resources\Concerns;

trait ResolvesTranslations
{
    protected function translated(string $attribute, ?string $locale = null, string $fallbackLocale = 'en'): mixed
    {
        $locale ??= app()->getLocale();
        $value = $this->{$attribute};

        if (is_array($value)) {
            return $value[$locale] ?? $value[$fallbackLocale] ?? reset($value) ?: null;
        }

        if (is_object($value)) {
            $value = (array) $value;
            return $value[$locale] ?? $value[$fallbackLocale] ?? reset($value) ?: null;
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded[$locale] ?? $decoded[$fallbackLocale] ?? reset($decoded) ?: null;
        }

        return $value;
    }
}
