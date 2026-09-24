<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\Services\RandevuTime;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Memories extends NativeComponent
{
    use AppliesLocale;

    /** @var array<int,array<string,mixed>> */
    public array $memories = [];

    public function mount(): void
    {
        $this->applyLocale();
        $this->refresh();
    }

    public function navTitle(): string
    {
        return __('randevu.memories_title');
    }

    public function refresh(): void
    {
        $this->memories = Randevu::memories()->get()->map(fn (Randevu $r) => $this->present($r))->all();
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
        ];
    }

    public function render(): View
    {
        return view('native.memories');
    }
}
