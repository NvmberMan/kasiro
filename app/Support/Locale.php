<?php

namespace App\Support;

class Locale
{
    /**
     * Default / source locale. Indonesian strings are the translation keys,
     * so this locale needs no JSON file.
     */
    public const DEFAULT = 'id';

    /**
     * Session key under which the platform (studio) locale is remembered for
     * guests and as a fast cache for authenticated users.
     */
    public const SESSION_KEY = 'locale';

    /**
     * @return array<string, string> locale code => label
     */
    public static function supported(): array
    {
        return config('app.supported_locales', ['id' => 'Indonesia']);
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::supported());
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::codes(), true);
    }

    /**
     * Coerce any value to a supported locale, falling back to the default.
     */
    public static function normalize(?string $locale): string
    {
        return self::isSupported($locale) ? $locale : self::DEFAULT;
    }

    public static function label(string $locale): string
    {
        return self::supported()[$locale] ?? $locale;
    }
}
