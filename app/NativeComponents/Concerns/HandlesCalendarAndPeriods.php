<?php

namespace App\NativeComponents\Concerns;

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

    public function calendarChanged(): void
    {
        if ($this->calendarIndex === 0) {
            $this->useGregorian();
        } else {
            $this->useHijri();
        }
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
