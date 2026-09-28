<?php

namespace App\NativeComponents\Concerns;

use App\Models\Randevu;
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

    public function toggleHours(): void
    {
        $this->show_hours = ! $this->show_hours;
    }

    /** Gregorian Y-m-d string, or null when the selection is not a real date. */
    public function gregorianDateString(): ?string
    {
        $month = RandevuTime::monthNumber($this->month);
        $day = (int) $this->day;
        $year = (int) $this->year;

        if ($month === null || ! checkdate($month, $day, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * Resolve both calendars from the active entry mode.
     *
     * @return array{occurs_on: ?string, hijri_year: ?int, hijri_month: ?int, hijri_day: ?int}|null
     */
    public function resolveDates(): ?array
    {
        if ($this->calendar_mode === 'hijri') {
            $month = RandevuHijri::monthNumber($this->h_month);
            $day = (int) $this->h_day;
            $year = (int) $this->h_year;

            if ($month === null || ! RandevuHijri::valid($year, $month, $day)) {
                return null;
            }

            [$gy, $gm, $gd] = RandevuHijri::toGregorian($year, $month, $day);

            return [
                'occurs_on' => sprintf('%04d-%02d-%02d', $gy, $gm, $gd),
                'hijri_year' => $year,
                'hijri_month' => $month,
                'hijri_day' => $day,
            ];
        }

        $date = $this->gregorianDateString();

        if ($date === null) {
            return null;
        }

        return array_merge(['occurs_on' => $date], Randevu::hijriTriple($date));
    }

    /**
     * 'H:i' from the Hour/Minute selects, or null when no hour is picked
     * (the leading "none" option). A picked hour with the minute left on
     * "none" means the top of that hour.
     */
    public function resolveTime(): ?string
    {
        if (! ctype_digit($this->hour)) {
            return null;
        }

        $minute = ctype_digit($this->minute) ? (int) $this->minute : 0;

        return sprintf('%02d:%02d', (int) $this->hour, $minute);
    }

    /**
     * Live preview of the card's phrase from the current selection, or
     * null while the selection is not a real date (the forms then show
     * the muted placeholder instead). Built on this trait's own
     * resolveDates()/resolveTime() so the preview can never drift
     * from what saving stores.
     */
    public function previewPhrase(): ?string
    {
        $dates = $this->resolveDates();

        if ($dates === null) {
            return null;
        }

        return RandevuTime::phraseFor(
            $dates['occurs_on'],
            $this->show_years,
            $this->show_months,
            $this->show_days,
            $this->show_hours,
            $this->resolveTime(),
        );
    }
}
