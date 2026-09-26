<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Dashboard extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;

    /** @var array<string,int> */
    public array $stats = [];

    public int $gregorianCount = 0;
    public int $hijriCount = 0;
    public bool $hasData = false;

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $this->refresh();
    }

    public function navTitle(): string
    {
        return __('randevu.dashboard_title');
    }

    public function refresh(): void
    {
        $today = Carbon::today();

        $upcoming = Randevu::upcoming()->count();
        $memories = Randevu::memories()->count();

        $this->stats = [
            'total' => $upcoming + $memories,
            'upcoming' => $upcoming,
            'memories' => $memories,
            'today' => Randevu::today()->count(),
            'this_month' => Randevu::whereBetween('occurs_on', [
                $today->copy()->startOfMonth()->toDateString(),
                $today->copy()->endOfMonth()->toDateString(),
            ])->count(),
        ];

        $this->hasData = $this->stats['total'] > 0;

        $this->gregorianCount = Randevu::where('entered_in', 'gregorian')->count();
        $this->hijriCount = Randevu::where('entered_in', 'hijri')->count();
    }

    public function render(): View
    {
        return view('native.dashboard');
    }
}
