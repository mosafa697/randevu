<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use App\Services\RandevuTime;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Follow extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;

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
        $this->appointments = Randevu::upcoming()->get()->map(fn (Randevu $r) => $this->present($r))->all();
    }

    /** @return array<string,mixed> */
    private function present(Randevu $randevu): array
    {
        return [
            'id' => $randevu->id,
            'title' => $randevu->title,
            'occurs_on' => $randevu->occurs_on->toDateString(),
            'absolute' => RandevuTime::absolute($randevu->occurs_on),
            'hijri' => $randevu->hijriLabel(),
            'phrase' => $randevu->relativePhrase(),
            'exact' => RandevuTime::exactSuffix($randevu->exactDayCount()),
            'note' => $randevu->note,
            'color' => $randevu->color,
            'is_today' => $randevu->isToday(),
        ];
    }

    public function render(): View
    {
        return view('native.follow');
    }
}
