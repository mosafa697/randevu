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

    public static function monthNumber(string $name): ?int
    {
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

    public static function format(int $year, int $month, int $day): string
    {
        $name = self::MONTH_NAMES[$month - 1] ?? '';

        return trim("{$day} {$name} {$year}");
    }

    /** @return list<string> */
    public static function yearOptions(): array
    {
        [$current] = self::fromGregorian((int) now()->year, (int) now()->month, (int) now()->day);

        return array_map(strval(...), range($current - 100, $current + 30));
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
