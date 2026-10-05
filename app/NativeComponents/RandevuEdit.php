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

class RandevuEdit extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;
    use HandlesCalendarAndPeriods;
    use PicksColor;
    use PicksCover;

    /**
     * Only scalar state lives on the component — a full Eloquent model in
     * public state may not survive native shared-memory sync, so the row
     * is reloaded for every action.
     */
    public int $randevuId = 0;

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

    public bool $confirmingDelete = false;

    /** @var array<string,string> */
    public array $errors = [];

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $this->randevuId = (int) $this->param('id');
        $randevu = $this->findOrFail();
        $this->title = $randevu->title;
        $this->note = (string) ($randevu->note ?? '');
        // setColor() also mirrors the hex into the slider channels.
        $this->setColor((string) ($randevu->color ?? ''));
        $this->cover_path = (string) ($randevu->cover_path ?? '');
        $this->calendar_mode = $randevu->entered_in === 'hijri' ? 'hijri' : 'gregorian';
        $this->calendarIndex = $this->calendar_mode === 'hijri' ? 1 : 0;
        $this->show_years = (bool) $randevu->show_years;
        $this->show_months = (bool) $randevu->show_months;
        $this->show_days = (bool) $randevu->show_days;
        $this->show_hours = (bool) $randevu->show_hours;
        $this->dayOptions = RandevuCreate::dayOptions();
        $this->monthOptions = RandevuTime::monthNames();
        $this->yearOptions = RandevuCreate::yearOptions();
        $this->hDayOptions = array_map(strval(...), range(1, 30));
        $this->hMonthOptions = RandevuHijri::monthNames();
        $this->hYearOptions = RandevuHijri::yearOptions();
        $this->hourOptions = RandevuCreate::hourOptions();
        $this->minuteOptions = RandevuCreate::minuteOptions();
        $this->hour = $randevu->occurs_time?->format('H') ?? __('randevu.time_none');
        $this->minute = $randevu->occurs_time?->format('i') ?? __('randevu.time_none');
        $this->day = (string) $randevu->occurs_on->day;
        $this->month = RandevuTime::monthNames()[$randevu->occurs_on->month - 1];
        $this->year = (string) $randevu->occurs_on->year;

        if ($randevu->hijri_year !== null && $randevu->hijri_month !== null && $randevu->hijri_day !== null) {
            $this->h_day = (string) $randevu->hijri_day;
            $this->h_month = RandevuHijri::monthName($randevu->hijri_month);
            $this->h_year = (string) $randevu->hijri_year;
        } else {
            [$hy, $hm, $hd] = RandevuHijri::fromGregorian(
                $randevu->occurs_on->year, $randevu->occurs_on->month, $randevu->occurs_on->day
            );
            $this->h_day = (string) $hd;
            $this->h_month = RandevuHijri::monthName($hm);
            $this->h_year = (string) $hy;
        }
    }

    public function navTitle(): string
    {
        return __('randevu.edit_title');
    }

    public function update(): void
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

        $randevu = $this->findOrFail();
        $previousCover = CoverImage::normalize($randevu->cover_path);
        $cover = CoverImage::store($cover, $coverMime);

        try {
            $randevu->update([
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
        } catch (\Throwable $e) {
            // Row untouched: an unchanged cover still backs it, only a fresh
            // copy would be orphaned.
            if ($cover !== $previousCover) {
                CoverImage::forget($cover);
            }

            throw $e;
        }

        if ($previousCover !== $cover) {
            CoverImage::forget($previousCover);
        }

        $this->replace('/follow');
    }

    public function askDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function destroy(): void
    {
        $randevu = $this->findOrFail();
        $cover = CoverImage::normalize($randevu->cover_path);

        $randevu->delete();

        CoverImage::forget($cover);

        $this->replace('/follow');
    }

    private function findOrFail(): Randevu
    {
        return Randevu::findOrFail($this->randevuId);
    }

    public function render(): View
    {
        return view('native.randevu-edit');
    }
}
