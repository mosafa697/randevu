<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class RandevuTime
{
    /** Gregorian month names in calendar order for the current locale. */
    public static function monthNames(): array
    {
        return (array) __('randevu.months');
    }

    public static function monthNumber(string $name): ?int
    {
        $index = array_search($name, self::monthNames(), true);

        return $index === false ? null : $index + 1;
    }

    /** Localized "23 September 2026" / "23 سبتمبر 2026" style date. */
    public static function absolute(CarbonInterface $date): string
    {
        $names = self::monthNames();
        $month = $names[$date->month - 1] ?? $date->format('F');

        return "{$date->day} {$month} {$date->year}";
    }

    /**
     * Signed day difference: positive = future, negative = past, 0 = today.
     */
    public static function dayCount(CarbonInterface|string $date, CarbonInterface|string|null $today = null): int
    {
        $target = $date instanceof CarbonInterface ? Carbon::parse($date->toDateString()) : Carbon::parse($date)->startOfDay();
        $base = $today instanceof CarbonInterface ? Carbon::parse($today->toDateString()) : ($today ? Carbon::parse($today)->startOfDay() : Carbon::today());

        return $base->diffInDays($target, false);
    }

    /**
     * Rounded human phrase plus exact count stays available via dayCount().
     *
     * Ranges: today/tomorrow/yesterday, days (2-29), months (30-364),
     * years (365+). Nouns follow the active locale (Egyptian Arabic shapes
     * included: يوم/يومين/أيام/يوم ...).
     */
    public static function phrase(CarbonInterface|string $date, CarbonInterface|string|null $today = null): string
    {
        $days = self::dayCount($date, $today);

        if ($days === 0) {
            return __('randevu.phrase_today');
        }

        $abs = abs($days);
        $future = $days > 0;

        if ($abs === 1) {
            return $future ? __('randevu.phrase_tomorrow') : __('randevu.phrase_yesterday');
        }

        if ($abs < 30) {
            return self::fill($future ? 'in_days' : 'days_ago', $abs, self::unit('day', $abs));
        }

        if ($abs < 365) {
            $months = (int) round($abs / 30);

            return self::fill($future ? 'in_months' : 'months_ago', $months, self::unit('month', $months));
        }

        $years = (int) round($abs / 365);

        return self::fill($future ? 'in_years' : 'years_ago', $years, self::unit('year', $years));
    }

    /** Exact-count suffix for cards, e.g. "كمان 5 أيام" / "In 5 days". */
    public static function exactSuffix(int $days): string
    {
        $abs = abs($days);

        return self::fill($days >= 0 ? 'exact_future' : 'exact_past', $abs, self::unit('day', $abs));
    }

    private static function fill(string $key, int $count, string $unit): string
    {
        $template = __("randevu.{$key}");

        // Arabic singular/dual absorb the numeral: "بعد شهر", "بعد يومين".
        if (app()->getLocale() === 'ar' && $count <= 2) {
            $template = trim(str_replace(':count', '', $template));
            $template = (string) preg_replace('/\s+/', ' ', $template);

            return str_replace(':unit', $unit, $template);
        }

        return __('randevu.'.$key, ['count' => $count, 'unit' => $unit]);
    }

    /** one (1) / two (2) / few (3-10) / many (11+) noun shape. */
    private static function unit(string $type, int $count): string
    {
        $shape = match (true) {
            $count === 1 => 'one',
            $count === 2 => 'two',
            $count <= 10 => 'few',
            default => 'many',
        };

        return __("randevu.{$type}_{$shape}");
    }
}
