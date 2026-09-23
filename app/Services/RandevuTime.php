<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class RandevuTime
{
    /** Gregorian month names in calendar order, for picker selects. */
    public const MONTH_NAMES = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    public static function monthNumber(string $name): ?int
    {
        $index = array_search($name, self::MONTH_NAMES, true);

        return $index === false ? null : $index + 1;
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
     * Future: Today / Tomorrow / In N days (2-29) / In N month(s) (30-364) / In N year(s) (365+)
     * Past:   Today / Yesterday / N days ago (2-29) / N month(s) ago / N year(s) ago
     */
    public static function phrase(CarbonInterface|string $date, CarbonInterface|string|null $today = null): string
    {
        $days = self::dayCount($date, $today);

        if ($days === 0) {
            return 'Today';
        }

        $abs = abs($days);
        $future = $days > 0;

        if ($abs === 1) {
            return $future ? 'Tomorrow' : 'Yesterday';
        }

        if ($abs < 30) {
            return $future ? "In {$abs} days" : "{$abs} days ago";
        }

        if ($abs < 365) {
            $months = (int) round($abs / 30);

            return $future
                ? "In {$months} month".($months === 1 ? '' : 's')
                : "{$months} month".($months === 1 ? '' : 's').' ago';
        }

        $years = (int) round($abs / 365);

        return $future
            ? "In {$years} year".($years === 1 ? '' : 's')
            : "{$years} year".($years === 1 ? '' : 's').' ago';
    }
}
