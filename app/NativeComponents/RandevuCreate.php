<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\PicksColor;
use App\Services\RandevuHijri;
use App\Services\RandevuTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class RandevuCreate extends NativeComponent
{
    use AppliesLocale;
    use PicksColor;

    public string $title = '';

    public string $note = '';

    public string $calendar_mode = 'gregorian';

    public string $day = '';

    public string $month = '';

    public string $year = '';

    public string $h_day = '';

    public string $h_month = '';

    public string $h_year = '';

    public bool $show_years = true;

    public bool $show_months = true;

    public bool $show_days = true;

    /** @var list<string> */
    public array $dayOptions = [];

    /** @var list<string> */
    public array $monthOptions = [];

    /** @var list<string> */
    public array $yearOptions = [];

    /** @var list<string> */
    public array $hDayOptions = [];

    /** @var list<string> */
    public array $hMonthOptions = [];

    /** @var list<string> */
    public array $hYearOptions = [];

    /** @var array<string,string> */
    public array $errors = [];

    public function mount(): void
    {
        $this->applyLocale();
        $today = now();
        $this->fillDateOptions();
        $this->day = (string) $today->day;
        $this->month = RandevuTime::monthNames()[$today->month - 1];
        $this->year = (string) $today->year;
        [$hy, $hm, $hd] = RandevuHijri::fromGregorian($today->year, $today->month, $today->day);
        $this->h_day = (string) $hd;
        $this->h_month = RandevuHijri::MONTH_NAMES[$hm - 1];
        $this->h_year = (string) $hy;
    }

    public function navTitle(): string
    {
        return __('randevu.create_title');
    }

    /** @return list<string> */
    public static function dayOptions(): array
    {
        return array_map(strval(...), range(1, 31));
    }

    /** @return list<string> */
    public static function yearOptions(): array
    {
        $year = now()->year;

        return array_map(strval(...), range($year - 100, $year + 30));
    }

    protected function fillDateOptions(): void
    {
        $this->dayOptions = self::dayOptions();
        $this->monthOptions = RandevuTime::monthNames();
        $this->yearOptions = self::yearOptions();
        $this->hDayOptions = array_map(strval(...), range(1, 30));
        $this->hMonthOptions = RandevuHijri::MONTH_NAMES;
        $this->hYearOptions = RandevuHijri::yearOptions();
    }

    public function useGregorian(): void
    {
        $this->calendar_mode = 'gregorian';
    }

    public function useHijri(): void
    {
        $this->calendar_mode = 'hijri';
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

    public function save(): void
    {
        $dates = $this->resolveDates();
        $color = Randevu::normalizeColor($this->color);

        $validator = Validator::make([
            'title' => $this->title,
            'occurs_on' => $dates['occurs_on'] ?? null,
            'color' => $color,
            'note' => $this->note ?: null,
            'entered_in' => $this->calendar_mode,
            'show_years' => $this->show_years,
            'show_months' => $this->show_months,
            'show_days' => $this->show_days,
        ], Randevu::rules());

        if ($validator->fails() || ! Randevu::hasAnyUnit($this->show_years, $this->show_months, $this->show_days)) {
            $this->errors = collect($validator->errors()->messages())
                ->mapWithKeys(fn ($msgs, $field) => [$field => (string) $msgs[0]])
                ->all();

            if (! Randevu::hasAnyUnit($this->show_years, $this->show_months, $this->show_days)) {
                $this->errors['period_units'] = __('randevu.period_units_required');
            }

            return;
        }

        $this->errors = [];

        Randevu::create([
            'title' => trim($this->title),
            'occurs_on' => $dates['occurs_on'],
            'color' => $color,
            'note' => $this->note !== '' ? trim($this->note) : null,
            'hijri_year' => $dates['hijri_year'],
            'hijri_month' => $dates['hijri_month'],
            'hijri_day' => $dates['hijri_day'],
            'entered_in' => $this->calendar_mode,
            'show_years' => $this->show_years,
            'show_months' => $this->show_months,
            'show_days' => $this->show_days,
        ]);

        $this->replace('/');
    }

    public function render(): View
    {
        return view('native.randevu-create');
    }
}
