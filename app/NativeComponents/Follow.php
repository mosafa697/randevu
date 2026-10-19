<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use App\NativeComponents\Concerns\FiltersEntries;
use App\Services\CoverImage;
use App\Services\RandevuNotificationRules;
use App\Services\RandevuTime;
use App\Support\Bidi;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Follow extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;
    use FiltersEntries;

    /** @var array<int,array<string,mixed>> */
    public array $appointments = [];

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $this->refresh();
    }

    public function navTitle(): string
    {
        return __('randevu.follow_title');
    }

    public function refresh(): void
    {
        $this->appointments = $this->applyEntryFilters(Randevu::upcoming())->get()->map(fn (Randevu $r) => $this->present($r))->all();
    }

    /** @return array<string,mixed> */
    private function present(Randevu $randevu): array
    {
        $days = RandevuTime::dayCount($randevu->occurs_on);

        return [
            'id' => $randevu->id,
            'title' => Bidi::isolate($randevu->title),
            'occurs_on' => $randevu->occurs_on->toDateString(),
            'absolute' => RandevuTime::absolute($randevu->occurs_on),
            'hijri' => $randevu->hijriLabel(),
            'phrase' => $randevu->relativePhrase(),
            'note' => Bidi::isolate($randevu->note === null ? null : Str::limit($randevu->note, 140)),
            'color' => $randevu->color,
            'cover' => CoverImage::src($randevu->cover_path),
            'is_today' => $randevu->isToday(),
            'days' => $days,
            'pct' => max(0.08, min(1.0, 1 - abs($days) / 30)),
            'tier' => RandevuTime::urgencyTier($days),
            'entered_in' => $randevu->entered_in,
            'notification_armed' => RandevuNotificationRules::isArmed($randevu),
        ];
    }

    public function render(): View
    {
        return view('native.follow');
    }
}
