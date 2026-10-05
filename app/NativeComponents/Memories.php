<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use App\NativeComponents\Concerns\FiltersEntries;
use App\Services\CoverImage;
use App\Services\RandevuTime;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Memories extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;
    use FiltersEntries;

    /** @var array<int,array<string,mixed>> */
    public array $memories = [];

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $this->refresh();
    }

    public function navTitle(): string
    {
        return __('randevu.memories_title');
    }

    public function refresh(): void
    {
        $this->memories = $this->applyEntryFilters(Randevu::memories())->get()->map(fn (Randevu $r) => $this->present($r))->all();
    }

    /** @return array<string,mixed> */
    private function present(Randevu $randevu): array
    {
        $days = RandevuTime::dayCount($randevu->occurs_on);

        return [
            'id' => $randevu->id,
            'title' => \App\Support\Bidi::isolate($randevu->title),
            'occurs_on' => $randevu->occurs_on->toDateString(),
            'absolute' => RandevuTime::absolute($randevu->occurs_on),
            'hijri' => $randevu->hijriLabel(),
            'phrase' => $randevu->relativePhrase(),
            'note' => \App\Support\Bidi::isolate($randevu->note === null ? null : \Illuminate\Support\Str::limit($randevu->note, 140)),
            'color' => $randevu->color,
            'cover' => CoverImage::src($randevu->cover_path),
            'days' => $days,
            'pct' => max(0.08, min(1.0, 1 - abs($days) / 30)),
            'entered_in' => $randevu->entered_in,
        ];
    }

    public function render(): View
    {
        return view('native.memories');
    }
}
