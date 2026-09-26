<?php

namespace App\NativeComponents\Concerns;

use App\Services\RandevuHijri;
use App\Services\RandevuTime;

trait HandlesCalendarAndPeriods
{
    public function useGregorian(): void
    {
        $this->calendar_mode = 'gregorian';
        $this->calendarIndex = 0;
    }

    public function useHijri(): void
    {
        $this->calendar_mode = 'hijri';
        $this->calendarIndex = 1;
    }

    /**
     * Model-sync hooks: every native:select change flows through
     * ComponentState::set(), which fires updated{Studly} — on device and
     * in tests alike. Recompute the Day options for the chosen Month/Year
     * and clamp a selected day that no longer exists (Feb 30 → Feb 28).
     */
    public function updatedMonth(): void
    {
        $this->clampGregorianDay();
    }

    public function updatedYear(): void
    {
        $this->clampGregorianDay();
    }

    public function updatedHMonth(): void
    {
        $this->clampHijriDay();
    }

    public function updatedHYear(): void
    {
        $this->clampHijriDay();
    }

    protected function clampGregorianDay(): void
    {
        $max = self::gregorianMaxDay($this->year ?? '', $this->month ?? '');

        $this->dayOptions = array_map(strval(...), range(1, $max));

        if ((int) ($this->day ?? '') > $max) {
            $this->day = (string) $max;
        }
    }

    protected function clampHijriDay(): void
    {
        $max = self::hijriMaxDay($this->h_year ?? '', $this->h_month ?? '');

        $this->hDayOptions = array_map(strval(...), range(1, $max));

        if ((int) ($this->h_day ?? '') > $max) {
            $this->h_day = (string) $max;
        }
    }

    public static function gregorianMaxDay(string $year, string $month): int
    {
        $monthNumber = RandevuTime::monthNumber($month);
        $yearNumber = (int) $year;

        if ($monthNumber === null || $yearNumber < 1) {
            return 31;
        }

        for ($day = 31; $day > 28; $day--) {
            if (checkdate($monthNumber, $day, $yearNumber)) {
                return $day;
            }
        }

        return 28;
    }

    public static function hijriMaxDay(string $year, string $month): int
    {
        $monthNumber = RandevuHijri::monthNumber($month);
        $yearNumber = (int) $year;

        if ($monthNumber === null || $yearNumber < 1) {
            return 30;
        }

        return RandevuHijri::daysInMonth($yearNumber, $monthNumber);
    }
    public function toggleYears(): void
    {
        $this->show_years = ! $this->show_years;
    }

    public function toggleMonths(): void
    {
        $this->show_months = ! $this->show_months;
    }

    public function toggleDays(): void
    {
        $this->show_days = ! $this->show_days;
    }
}
