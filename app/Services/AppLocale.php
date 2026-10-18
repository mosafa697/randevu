<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

/**
 * Applies the device-stored language, falling back to the configured default.
 * Fresh installs default to Egyptian Arabic (config), English is the fallback locale.
 * Modern Standard Arabic ('ar_fusha') sits alongside Egyptian ('ar').
 */
class AppLocale
{
    public const KEY = 'locale';

    public const SUPPORTED = ['ar', 'ar_fusha', 'en'];

    public static function current(): string
    {
        $stored = self::storeAvailable() ? Setting::get(self::KEY) : null;

        if (in_array($stored, self::SUPPORTED, true)) {
            return $stored;
        }

        $configured = (string) config('app.locale', 'ar');

        return in_array($configured, self::SUPPORTED, true) ? $configured : 'ar';
    }

    public static function apply(): string
    {
        $locale = self::current();
        App::setLocale($locale);
        AppFonts::apply($locale);

        return $locale;
    }

    public static function persist(string $locale): string
    {
        $locale = in_array($locale, self::SUPPORTED, true) ? $locale : 'ar';

        if (self::storeAvailable()) {
            Setting::set(self::KEY, $locale);
        }

        App::setLocale($locale);
        AppFonts::apply($locale);

        return $locale;
    }

    public static function isRtl(?string $locale = null): bool
    {
        return self::isArabic($locale);
    }

    public static function isArabic(?string $locale = null): bool
    {
        return in_array($locale ?? self::current(), ['ar', 'ar_fusha'], true);
    }

    private static function storeAvailable(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
