<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Follow extends NativeComponent
{
    /** @var array<int,array<string,mixed>> */
    public array $upcoming = [];

    /** @var array<int,array<string,mixed>> */
    public array $memories = [];

    /** @var array<int,array<string,mixed>> */
    public array $today = [];

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->today = Randevu::today()->orderBy('title')->get()->map(fn (Randevu $r) => $this->present($r))->all();
        $this->upcoming = Randevu::upcoming()->whereDate('occurs_on', '>', today())->get()->map(fn (Randevu $r) => $this->present($r))->all();
        $this->memories = Randevu::memories()->get()->map(fn (Randevu $r) => $this->present($r))->all();
    }

    /** @return array<string,mixed> */
    private function present(Randevu $randevu): array
    {
        return [
            'id' => $randevu->id,
            'title' => $randevu->title,
            'occurs_on' => $randevu->occurs_on->toDateString(),
            'absolute' => $randevu->occurs_on->format('d M Y'),
            'phrase' => $randevu->relativePhrase(),
            'days' => $randevu->exactDayCount(),
            'note' => $randevu->note,
            'is_today' => $randevu->isToday(),
        ];
    }

    public function render(): View
    {
        return view('native.follow');
    }
}
