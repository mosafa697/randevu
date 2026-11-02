<?php

namespace App\NativeComponents\Concerns;

use App\Models\Randevu;
use App\NativeComponents\RandevuCreate;
use App\Services\AppLocale;
use App\Services\RandevuHijri;
use App\Services\RandevuTime;
use Illuminate\Support\Carbon;

trait HandlesCalendarAndPeriods
{
    public int $calendarViewMonth = 1;

    public int $calendarViewYear = 1;

    public int $calendarSelectedDay = 1;

    public string $calendarYearSelection = '';

    public string $calendarMonthHeading = '';

    public string $calendarYearLabel = '';

    public string $calendarPreviousIosIcon = 'chevron.left';

    public string $calendarPreviousAndroidIcon = 'chevron_left';

    public string $calendarNextIosIcon = 'chevron.right';

    public string $calendarNextAndroidIcon = 'chevron_right';

    public string $calendarSelectedDateLabel = '';

    public int $calendarHighlightedDay = 0;

    public bool $showCalendarMonthPicker = false;

    /** @var list<list<int|null>> */
    public array $calendarWeeks = [];

    /** @var list<string> */
    public array $calendarWeekdayNames = [];

    /** @var list<string> */
    public array $calendarMonthNames = [];

    /** @var list<string> */
    public array $calendarYearOptions = [];

    public function useGregorian(): void
    {
        $this->calendar_mode = 'gregorian';
        $this->calendarIndex = 0;
        $this->syncCalendarViewToSelection();
    }

    public function useHijri(): void
    {
        $this->calendar_mode = 'hijri';
        $this->calendarIndex = 1;
        $this->syncCalendarViewToSelection();
    }

    public function toggleCalendarMonthPicker(): void
    {
        $this->showCalendarMonthPicker = ! $this->showCalendarMonthPicker;
    }

    public function previousCalendarMonth(): void
    {
        $this->moveCalendarMonth(-1);
    }

    public function nextCalendarMonth(): void
    {
        $this->moveCalendarMonth(1);
    }

    public function updatedCalendarYearSelection(): void
    {
        if (! ctype_digit($this->calendarYearSelection)
            || ! in_array($this->calendarYearSelection, $this->calendarYearOptions, true)) {
            return;
        }

        $this->calendarViewYear = (int) $this->calendarYearSelection;
        $this->refreshCalendarWeeks();
    }

    public function selectCalendarMonth01(): void
    {
        $this->selectCalendarMonth(1);
    }

    public function selectCalendarMonth02(): void
    {
        $this->selectCalendarMonth(2);
    }

    public function selectCalendarMonth03(): void
    {
        $this->selectCalendarMonth(3);
    }

    public function selectCalendarMonth04(): void
    {
        $this->selectCalendarMonth(4);
    }

    public function selectCalendarMonth05(): void
    {
        $this->selectCalendarMonth(5);
    }

    public function selectCalendarMonth06(): void
    {
        $this->selectCalendarMonth(6);
    }

    public function selectCalendarMonth07(): void
    {
        $this->selectCalendarMonth(7);
    }

    public function selectCalendarMonth08(): void
    {
        $this->selectCalendarMonth(8);
    }

    public function selectCalendarMonth09(): void
    {
        $this->selectCalendarMonth(9);
    }

    public function selectCalendarMonth10(): void
    {
        $this->selectCalendarMonth(10);
    }

    public function selectCalendarMonth11(): void
    {
        $this->selectCalendarMonth(11);
    }

    public function selectCalendarMonth12(): void
    {
        $this->selectCalendarMonth(12);
    }

    public function selectCalendarDay01(): void
    {
        $this->selectCalendarDay(1);
    }

    public function selectCalendarDay02(): void
    {
        $this->selectCalendarDay(2);
    }

    public function selectCalendarDay03(): void
    {
        $this->selectCalendarDay(3);
    }

    public function selectCalendarDay04(): void
    {
        $this->selectCalendarDay(4);
    }

    public function selectCalendarDay05(): void
    {
        $this->selectCalendarDay(5);
    }

    public function selectCalendarDay06(): void
    {
        $this->selectCalendarDay(6);
    }

    public function selectCalendarDay07(): void
    {
        $this->selectCalendarDay(7);
    }

    public function selectCalendarDay08(): void
    {
        $this->selectCalendarDay(8);
    }

    public function selectCalendarDay09(): void
    {
        $this->selectCalendarDay(9);
    }

    public function selectCalendarDay10(): void
    {
        $this->selectCalendarDay(10);
    }

    public function selectCalendarDay11(): void
    {
        $this->selectCalendarDay(11);
    }

    public function selectCalendarDay12(): void
    {
        $this->selectCalendarDay(12);
    }

    public function selectCalendarDay13(): void
    {
        $this->selectCalendarDay(13);
    }

    public function selectCalendarDay14(): void
    {
        $this->selectCalendarDay(14);
    }

    public function selectCalendarDay15(): void
    {
        $this->selectCalendarDay(15);
    }

    public function selectCalendarDay16(): void
    {
        $this->selectCalendarDay(16);
    }

    public function selectCalendarDay17(): void
    {
        $this->selectCalendarDay(17);
    }

    public function selectCalendarDay18(): void
    {
        $this->selectCalendarDay(18);
    }

    public function selectCalendarDay19(): void
    {
        $this->selectCalendarDay(19);
    }

    public function selectCalendarDay20(): void
    {
        $this->selectCalendarDay(20);
    }

    public function selectCalendarDay21(): void
    {
        $this->selectCalendarDay(21);
    }

    public function selectCalendarDay22(): void
    {
        $this->selectCalendarDay(22);
    }

    public function selectCalendarDay23(): void
    {
        $this->selectCalendarDay(23);
    }

    public function selectCalendarDay24(): void
    {
        $this->selectCalendarDay(24);
    }

    public function selectCalendarDay25(): void
    {
        $this->selectCalendarDay(25);
    }

    public function selectCalendarDay26(): void
    {
        $this->selectCalendarDay(26);
    }

    public function selectCalendarDay27(): void
    {
        $this->selectCalendarDay(27);
    }

    public function selectCalendarDay28(): void
    {
        $this->selectCalendarDay(28);
    }

    public function selectCalendarDay29(): void
    {
        $this->selectCalendarDay(29);
    }

    public function selectCalendarDay30(): void
    {
        $this->selectCalendarDay(30);
    }

    public function selectCalendarDay31(): void
    {
        $this->selectCalendarDay(31);
    }

    private function syncCalendarViewToSelection(): void
    {
        if ($this->calendar_mode === 'hijri') {
            $this->calendarViewYear = max(1, (int) $this->h_year);
            $this->calendarViewMonth = RandevuHijri::monthNumber($this->h_month) ?? 1;
            $this->calendarSelectedDay = max(1, (int) $this->h_day);
            $this->calendarMonthNames = RandevuHijri::monthNames();
            $this->calendarYearOptions = RandevuHijri::yearOptions();
        } else {
            $this->calendarViewYear = max(1, (int) $this->year);
            $this->calendarViewMonth = RandevuTime::monthNumber($this->month) ?? 1;
            $this->calendarSelectedDay = max(1, (int) $this->day);
            $this->calendarMonthNames = RandevuTime::monthNames();
            $this->calendarYearOptions = RandevuCreate::yearOptions();
        }

        $this->calendarYearSelection = (string) $this->calendarViewYear;
        $this->calendarWeekdayNames = array_values((array) __('randevu.calendar_weekdays'));
        $this->showCalendarMonthPicker = false;
        $this->refreshCalendarWeeks();
    }

    private function selectCalendarMonth(int $month): void
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException('Calendar month must be between 1 and 12.');
        }

        $this->calendarViewMonth = $month;
        $this->showCalendarMonthPicker = false;
        $this->refreshCalendarWeeks();
    }

    private function moveCalendarMonth(int $offset): void
    {
        if ($this->calendar_mode === 'hijri') {
            $month = $this->calendarViewMonth + $offset;

            if ($month < 1) {
                $this->calendarViewYear = max(1, $this->calendarViewYear - 1);
                $month = 12;
            } elseif ($month > 12) {
                $this->calendarViewYear++;
                $month = 1;
            }

            $this->calendarViewMonth = $month;
        } else {
            $month = Carbon::create($this->calendarViewYear, $this->calendarViewMonth, 1)
                ->addMonthsNoOverflow($offset);

            $this->calendarViewYear = max(1, min(9999, $month->year));
            $this->calendarViewMonth = $month->month;
        }

        $this->calendarYearSelection = (string) $this->calendarViewYear;
        $this->refreshCalendarWeeks();
    }

    private function refreshCalendarWeeks(): void
    {
        if ($this->calendar_mode === 'hijri') {
            $dayCount = RandevuHijri::daysInMonth($this->calendarViewYear, $this->calendarViewMonth);
            [$gregorianYear, $gregorianMonth, $gregorianDay] = RandevuHijri::toGregorian(
                $this->calendarViewYear,
                $this->calendarViewMonth,
                1,
            );
        } else {
            $dayCount = Carbon::create($this->calendarViewYear, $this->calendarViewMonth, 1)->daysInMonth;
            $gregorianYear = $this->calendarViewYear;
            $gregorianMonth = $this->calendarViewMonth;
            $gregorianDay = 1;
        }

        $weekday = Carbon::create($gregorianYear, $gregorianMonth, $gregorianDay)->dayOfWeek;
        $isRtl = AppLocale::isRtl();
        $firstWeekday = $isRtl ? ($weekday + 1) % 7 : $weekday;
        $this->calendarPreviousIosIcon = $isRtl ? 'chevron.right' : 'chevron.left';
        $this->calendarPreviousAndroidIcon = $isRtl ? 'chevron_right' : 'chevron_left';
        $this->calendarNextIosIcon = $isRtl ? 'chevron.left' : 'chevron.right';
        $this->calendarNextAndroidIcon = $isRtl ? 'chevron_left' : 'chevron_right';
        $days = array_fill(0, 42, null);

        for ($day = 1; $day <= $dayCount; $day++) {
            $days[$firstWeekday + $day - 1] = $day;
        }

        $this->calendarWeeks = array_chunk($days, 7);
        $this->calendarMonthHeading = $this->calendarMonthNames[$this->calendarViewMonth - 1] ?? '';
        $this->calendarYearLabel = (string) $this->calendarViewYear;

        if ($this->calendar_mode === 'hijri') {
            $this->calendarYearLabel .= ' '.RandevuHijri::hijriSuffix();
            $this->calendarSelectedDateLabel = trim("{$this->h_day} {$this->h_month} {$this->h_year} ".RandevuHijri::hijriSuffix());
            $this->calendarHighlightedDay = RandevuHijri::monthNumber($this->h_month) === $this->calendarViewMonth
                && (int) $this->h_year === $this->calendarViewYear
                    ? (int) $this->h_day
                    : 0;
        } else {
            $this->calendarSelectedDateLabel = trim("{$this->day} {$this->month} {$this->year}");
            $this->calendarHighlightedDay = RandevuTime::monthNumber($this->month) === $this->calendarViewMonth
                && (int) $this->year === $this->calendarViewYear
                    ? (int) $this->day
                    : 0;
        }
    }

    private function selectCalendarDay(int $day): void
    {
        if ($this->calendar_mode === 'hijri') {
            if (! RandevuHijri::valid($this->calendarViewYear, $this->calendarViewMonth, $day)) {
                throw new \InvalidArgumentException('The selected day is not in this Hijri month.');
            }

            $this->h_day = (string) $day;
            $this->h_month = RandevuHijri::monthName($this->calendarViewMonth);
            $this->h_year = (string) $this->calendarViewYear;
            [$year, $month, $gregorianDay] = RandevuHijri::toGregorian(
                $this->calendarViewYear,
                $this->calendarViewMonth,
                $day,
            );
            $this->day = (string) $gregorianDay;
            $this->month = RandevuTime::monthNames()[$month - 1];
            $this->year = (string) $year;
        } else {
            if (! checkdate($this->calendarViewMonth, $day, $this->calendarViewYear)) {
                throw new \InvalidArgumentException('The selected day is not in this Gregorian month.');
            }

            $this->day = (string) $day;
            $this->month = RandevuTime::monthNames()[$this->calendarViewMonth - 1];
            $this->year = (string) $this->calendarViewYear;
            [$year, $month, $hijriDay] = RandevuHijri::fromGregorian(
                $this->calendarViewYear,
                $this->calendarViewMonth,
                $day,
            );
            $this->h_day = (string) $hijriDay;
            $this->h_month = RandevuHijri::monthName($month);
            $this->h_year = (string) $year;
        }

        $this->calendarSelectedDay = $day;
        $this->calendarYearSelection = (string) $this->calendarViewYear;
        $this->clampGregorianDay();
        $this->clampHijriDay();
        $this->refreshCalendarWeeks();
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

    public function setToday(): void
    {
        $this->setDateChips(0);
        $this->syncCalendarViewToSelection();
    }

    public function setPlus7(): void
    {
        $this->setDateChips(7);
        $this->syncCalendarViewToSelection();
    }

    public function setPlus30(): void
    {
        $this->setDateChips(30);
        $this->syncCalendarViewToSelection();
    }

    /**
     * Quick-set chip handler: writes the date N days out into BOTH
     * calendars' props (same formats mount() prefills), so switching
     * calendar mode afterwards still shows the chosen date. Refreshes
     * both Day option lists for the written months — direct prop writes
     * bypass the updated{Month,Year} hooks that normally do this.
     */
    protected function setDateChips(int $days): void
    {
        $date = now()->addDays($days);

        $this->day = (string) $date->day;
        $this->month = RandevuTime::monthNames()[$date->month - 1];
        $this->year = (string) $date->year;

        [$hy, $hm, $hd] = RandevuHijri::fromGregorian($date->year, $date->month, $date->day);
        $this->h_day = (string) $hd;
        $this->h_month = RandevuHijri::monthName($hm);
        $this->h_year = (string) $hy;

        $this->clampGregorianDay();
        $this->clampHijriDay();
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
