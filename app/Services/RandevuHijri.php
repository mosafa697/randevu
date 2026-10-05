<?php

namespace App\Services;

/**
 * Tabular (arithmetical) Islamic calendar conversion.
 *
 * Pure PHP on purpose: it must run identically on the dev machine and in
 * NativePHP's embedded PHP on device, where the intl extension may be absent.
 *
 * Caveat: the tabular calendar can differ by ±1 day from the observed
 * Umm al-Qura calendar for some dates. Gregorian stays the source of truth
 * for sorting and relative phrases; Hijri is stored alongside for display
 * and entry.
 */
class RandevuHijri
{
    /** Hijri month names in calendar order. */
    public const MONTH_NAMES = [
        'محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني',
        'جمادى الأولى', 'جمادى الثانية', 'رجب', 'شعبان',
        'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة',
    ];

    /** Leap years within each 30-year cycle. */
    public const LEAP_YEARS = [2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29];

    /**
     * Hijri month names for the current locale. Falls back to the Arabic
     * names when the translation is missing or incomplete — same pattern
     * as RandevuTime::monthNames() for Gregorian months.
     *
     * @return list<string>
     */
    public static function monthNames(): array
    {
        try {
            $names = (array) __('randevu.hijri_months');
        } catch (\Throwable) {
            // No translator booted (plain unit context): Arabic fallback.
            return self::MONTH_NAMES;
        }

        $valid = count($names) === 12;

        foreach ($names as $name) {
            $valid = $valid && is_string($name) && $name !== '';
        }

        return $valid ? array_values($names) : self::MONTH_NAMES;
    }

    /** Locale-aware month name (1-12), Arabic fallback. Empty when out of range. */
    public static function monthName(int $month): string
    {
        return self::monthNames()[$month - 1] ?? '';
    }

    /** Era suffix for display ("هـ" / "AH"), Arabic fallback. */
    public static function hijriSuffix(): string
    {
        try {
            $suffix = __('randevu.hijri_suffix');
        } catch (\Throwable) {
            return 'هـ';
        }

        return is_string($suffix) && $suffix !== '' && $suffix !== 'randevu.hijri_suffix'
            ? $suffix
            : 'هـ';
    }

    public static function monthNumber(string $name): ?int
    {
        $index = array_search($name, self::monthNames(), true);

        if ($index !== false) {
            return $index + 1;
        }

        // Stale selection or missing translation: resolve the Arabic name.
        $index = array_search($name, self::MONTH_NAMES, true);

        return $index === false ? null : $index + 1;
    }

    public static function isLeapYear(int $year): bool
    {
        return in_array((($year - 1) % 30) + 1, self::LEAP_YEARS, true);
    }

    public static function daysInMonth(int $year, int $month): int
    {
        if ($month < 1 || $month > 12) {
            return 0;
        }

        if ($month === 12) {
            return self::isLeapYear($year) ? 30 : 29;
        }

        return $month % 2 === 1 ? 30 : 29;
    }

    public static function valid(int $year, int $month, int $day): bool
    {
        return $year > 0 && $day >= 1 && $day <= self::daysInMonth($year, $month);
    }

    /** Gregorian Y-M-D → [hijriY, hijriM, hijriD]. */
    public static function fromGregorian(int $year, int $month, int $day): array
    {
        return self::jdToIslamic(self::gregorianToJd($year, $month, $day));
    }

    /** Hijri Y-M-D → [gregorianY, gregorianM, gregorianD]. */
    public static function toGregorian(int $year, int $month, int $day): array
    {
        return self::jdToGregorian(self::islamicToJd($year, $month, $day));
    }

    /**
     * "12 ربيع الثاني 1448" in Arabic, "12 Rabi' al-Thani 1448" in
     * English. Western digits in both modes (matches existing display).
     */
    public static function format(int $year, int $month, int $day): string
    {
        $name = self::monthName($month);

        return trim("{$day} {$name} {$year}");
    }

    /**
     * Newest-first, mirroring the Gregorian year list: the native
     * dropdown opens at the top with no scroll-to-selection support,
     * so the current Hijri year sits near the top.
     *
     * @return list<string>
     */
    public static function yearOptions(): array
    {
        [$current] = self::fromGregorian((int) now()->year, (int) now()->month, (int) now()->day);

        return array_map(strval(...), range($current + 30, $current - 100));
    }

    private static function gregorianToJd(int $year, int $month, int $day): int
    {
        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;

        return $day + intdiv(153 * $m + 2, 5) + 365 * $y + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400) - 32045;
    }

    private static function jdToGregorian(int $jd): array
    {
        $a = $jd + 32044;
        $b = intdiv(4 * $a + 3, 146097);
        $c = $a - intdiv(146097 * $b, 4);
        $d = intdiv(4 * $c + 3, 1461);
        $e = $c - intdiv(1461 * $d, 4);
        $m = intdiv(5 * $e + 2, 153);

        return [
            100 * $b + $d - 4800 + intdiv($m, 10),
            $m + 3 - 12 * intdiv($m, 10),
            $e - intdiv(153 * $m + 2, 5) + 1,
        ];
    }

    private static function islamicToJd(int $year, int $month, int $day): int
    {
        return (int) ($day
            + ceil(29.5 * ($month - 1))
            + ($year - 1) * 354
            + floor((3 + 11 * $year) / 30)
            + 1948440 - 1);
    }

    /** @return array{int,int,int} */
    private static function jdToIslamic(int $jd): array
    {
        $l = $jd - 1948440 + 10632;
        $n = intdiv($l - 1, 10631);
        $l = $l - 10631 * $n + 354;
        $j = intdiv(10985 - $l, 5316) * intdiv(50 * $l, 17719)
            + intdiv($l, 5670) * intdiv(43 * $l, 15238);
        $l = $l
            - intdiv(30 - $j, 15) * intdiv(17719 * $j, 50)
            - intdiv($j, 16) * intdiv(15238 * $j, 43)
            + 29;
        $month = intdiv(24 * $l, 709);
        $day = $l - intdiv(709 * $month, 24);
        $year = 30 * $n + $j - 30;

        return [$year, $month, $day];
    }
}
