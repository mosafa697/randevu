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
        [$target, $base] = self::bounds($date, $today);

        return $base->diffInDays($target, false);
    }

    /** @return array{0: Carbon, 1: Carbon} [target, base] both at start of day */
    private static function bounds(CarbonInterface|string $date, CarbonInterface|string|null $today): array
    {
        $target = $date instanceof CarbonInterface ? Carbon::parse($date->toDateString()) : Carbon::parse($date)->startOfDay();
        $base = $today instanceof CarbonInterface ? Carbon::parse($today->toDateString()) : ($today ? Carbon::parse($today)->startOfDay() : Carbon::today());

        return [$target, $base];
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

    /**
     * Per-appointment formatter: renders the distance using only the units
     * the user ticked for that randevu (any non-empty subset of
     * years / months / days).
     *
     * Rules (documented approximation basis):
     * - Today / Tomorrow / Yesterday stay special regardless of units.
     * - A single unit uses rounded totals: exact day count for days,
     *   round(days / 30) for months, round(days / 365) for years (min 1).
     * - Multiple units use the exact calendar breakdown (Carbon diff — real
     *   month lengths and leap years). A switched-off middle unit rolls
     *   down nominally (years × 12 into months, months × 30 into days);
     *   a switched-off days tail is dropped. When every shown part is
     *   zero, the smallest shown unit renders rounded with a floor of 1.
     * - All units off is invalid input; defensively falls back to `phrase()`.
     */
    public static function phraseFor(
        CarbonInterface|string $date,
        bool|int|null $showYears,
        bool|int|null $showMonths,
        bool|int|null $showDays,
        CarbonInterface|string|null $today = null,
    ): string {
        $showYears = (bool) $showYears;
        $showMonths = (bool) $showMonths;
        $showDays = (bool) $showDays;

        if (! $showYears && ! $showMonths && ! $showDays) {
            return self::phrase($date, $today);
        }

        $days = self::dayCount($date, $today);

        if ($days === 0) {
            return __('randevu.phrase_today');
        }

        $abs = abs($days);
        $future = $days > 0;

        if ($abs === 1) {
            return $future ? __('randevu.phrase_tomorrow') : __('randevu.phrase_yesterday');
        }

        $onCount = (int) $showYears + (int) $showMonths + (int) $showDays;

        if ($onCount === 1) {
            if ($showDays) {
                return self::fill($future ? 'in_days' : 'days_ago', $abs, self::unit('day', $abs));
            }

            if ($showMonths) {
                return self::forcedUnit($abs, 30, 'month', $future);
            }

            return self::forcedUnit($abs, 365, 'year', $future);
        }

        [$target, $base] = self::bounds($date, $today);

        $start = $future ? $base->copy() : $target->copy();
        $end = $future ? $target->copy() : $base->copy();
        $interval = $start->diff($end);

        $years = (int) $interval->y;
        $months = (int) $interval->m;
        $restDays = (int) $interval->d;

        if (! $showYears) {
            $months += $years * 12;
            $years = 0;
        }

        if (! $showMonths) {
            $restDays += $months * 30;
            $months = 0;
        }

        $parts = [];

        if ($showYears && $years > 0) {
            $parts[] = self::combinedPart('year', $years);
        }

        if ($showMonths && $months > 0) {
            $parts[] = self::combinedPart('month', $months);
        }

        if ($showDays && $restDays > 0) {
            $parts[] = self::combinedPart('day', $restDays);
        }

        if ($parts === []) {
            // Span smaller than the smallest shown unit: round it, min 1.
            $smallest = $showDays ? 'day' : ($showMonths ? 'month' : 'year');
            $divisor = $smallest === 'day' ? 1 : ($smallest === 'month' ? 30 : 365);

            return self::forcedUnit($abs, $divisor, $smallest, $future);
        }

        $joined = implode(__('randevu.combined_separator'), $parts);

        return __('randevu.'.($future ? 'combined_future' : 'combined_past'), ['parts' => $joined]);
    }

    /** Forced-unit rendering with a floor of 1 (never "0 months"). */
    private static function forcedUnit(int $absDays, int $divisor, string $type, bool $future): string
    {
        $count = max(1, (int) round($absDays / $divisor));

        return self::fill(
            $future ? "in_{$type}s" : "{$type}s_ago",
            $count,
            self::unit($type, $count)
        );
    }

    /**
     * One "N unit" chunk of a combined breakdown. Like fill(), Arabic
     * singular/dual absorb the numeral ("شهر"، "شهرين").
     */
    private static function combinedPart(string $type, int $count): string
    {
        $unit = self::unit($type, $count);

        if (app()->getLocale() === 'ar' && $count <= 2) {
            return $unit;
        }

        return "{$count} {$unit}";
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
