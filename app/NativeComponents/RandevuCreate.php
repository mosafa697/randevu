<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use App\NativeComponents\Concerns\HandlesCalendarAndPeriods;
use App\NativeComponents\Concerns\PicksColor;
use App\NativeComponents\Concerns\PicksCover;
use App\Services\CoverImage;
use App\Services\RandevuHijri;
use App\Services\RandevuTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class RandevuCreate extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;
    use HandlesCalendarAndPeriods;
    use PicksColor;
    use PicksCover;

    public string $title = '';

    public string $note = '';

    public string $calendar_mode = 'gregorian';

    public int $calendarIndex = 0;

    public string $day = '';

    public string $month = '';

    public string $year = '';

    public string $h_day = '';

    public string $h_month = '';

    public string $h_year = '';

    public string $hour = '';

    public string $minute = '';

    public bool $show_years = true;

    public bool $show_months = true;

    public bool $show_days = true;

    public bool $show_hours = false;

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

    /** @var list<string> */
    public array $hourOptions = [];

    /** @var list<string> */
    public array $minuteOptions = [];

    /** @var array<string,string> */
    public array $errors = [];

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $today = now();
        $this->fillDateOptions();
        $this->day = (string) $today->day;
        $this->month = RandevuTime::monthNames()[$today->month - 1];
        $this->year = (string) $today->year;
        [$hy, $hm, $hd] = RandevuHijri::fromGregorian($today->year, $today->month, $today->day);
        $this->h_day = (string) $hd;
        $this->h_month = RandevuHijri::monthName($hm);
        $this->h_year = (string) $hy;
        $this->hour = __('randevu.time_none');
        $this->minute = __('randevu.time_none');
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

    /**
     * Newest-first: the native dropdown opens at the top of the list with
     * no scroll-to-selection support (4.5.2 Select API + renderers
     * verified), so the current year sits near the top (index 30) instead
     * of ~76% down an ascending century-long list.
     *
     * @return list<string>
     */
    public static function yearOptions(): array
    {
        $year = now()->year;

        return array_map(strval(...), range($year + 30, $year - 100));
    }

    /**
     * Leading "none" option = no time-of-day. Values are zero-padded
     * to match the select display ('09', not '9').
     *
     * @return list<string>
     */
    public static function hourOptions(): array
    {
        return [__('randevu.time_none'), ...array_map(
            static fn (int $hour): string => sprintf('%02d', $hour),
            range(0, 23)
        )];
    }

    /**
     * Leading "none" option: with an hour picked, no minute means the top
     * of that hour.
     *
     * @return list<string>
     */
    public static function minuteOptions(): array
    {
        return [__('randevu.time_none'), ...array_map(
            static fn (int $minute): string => sprintf('%02d', $minute),
            range(0, 59)
        )];
    }

    protected function fillDateOptions(): void
    {
        $this->dayOptions = self::dayOptions();
        $this->monthOptions = RandevuTime::monthNames();
        $this->yearOptions = self::yearOptions();
        $this->hDayOptions = array_map(strval(...), range(1, 30));
        $this->hMonthOptions = RandevuHijri::monthNames();
        $this->hYearOptions = RandevuHijri::yearOptions();
        $this->hourOptions = self::hourOptions();
        $this->minuteOptions = self::minuteOptions();
    }

    public function save(): void
    {
        $dates = $this->resolveDates();
        $color = Randevu::normalizeColor($this->color);
        $time = $this->resolveTime();
        $cover = CoverImage::normalize($this->cover_path);
        $coverMime = CoverImage::normalizeMime($this->cover_mime);
        $coverError = CoverImage::validate($cover, $coverMime);

        $validator = Validator::make([
            'title' => $this->title,
            'occurs_on' => $dates['occurs_on'] ?? null,
            'occurs_time' => $time,
            'color' => $color,
            'cover_path' => $cover,
            'note' => $this->note ?: null,
            'entered_in' => $this->calendar_mode,
            'show_years' => $this->show_years,
            'show_months' => $this->show_months,
            'show_days' => $this->show_days,
            'show_hours' => $this->show_hours,
        ], Randevu::rules());

        if ($validator->fails() || $coverError !== null || ! Randevu::hasAnyUnit($this->show_years, $this->show_months, $this->show_days, $this->show_hours)) {
            $this->errors = collect($validator->errors()->messages())
                ->mapWithKeys(fn ($msgs, $field) => [$field => (string) $msgs[0]])
                ->all();

            if ($coverError !== null) {
                $this->errors['cover'] = $coverError === 'size'
                    ? __('randevu.cover_error_size')
                    : $this->coverTypeMessage($cover, $coverMime);
            }

            if (! Randevu::hasAnyUnit($this->show_years, $this->show_months, $this->show_days, $this->show_hours)) {
                $this->errors['period_units'] = __('randevu.period_units_required');
            }

            return;
        }

        $this->errors = [];

        $cover = CoverImage::store($cover, $coverMime);

        try {
            $randevu = Randevu::create([
                'title' => trim($this->title),
                'occurs_on' => $dates['occurs_on'],
                'occurs_time' => $time,
                'color' => $color,
                'cover_path' => $cover,
                'note' => $this->note !== '' ? trim($this->note) : null,
                'hijri_year' => $dates['hijri_year'],
                'hijri_month' => $dates['hijri_month'],
                'hijri_day' => $dates['hijri_day'],
                'entered_in' => $this->calendar_mode,
                'show_years' => $this->show_years,
                'show_months' => $this->show_months,
                'show_days' => $this->show_days,
                'show_hours' => $this->show_hours,
                ]);

            $this->replace("/details/$randevu->id");
        } catch (\Throwable $e) {
            // Row not written — the just-copied cover file would be orphaned.
            CoverImage::forget($cover);

            throw $e;
        }
    }

    public function render(): View
    {
        return view('native.randevu-create');
    }
}
