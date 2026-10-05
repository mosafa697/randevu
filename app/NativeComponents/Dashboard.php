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

    /**
     * Entry-calendar donut (ring only — the legend is native text beside
     * it). Built at render so slice colors always track the active
     * forced palette.
     */
    public function splitDonutHtml(): string
    {
        $splitTotal = $this->gregorianCount + $this->hijriCount;
        $r = 44;
        $c = 2 * M_PI * $r;
        $gregLen = $splitTotal > 0 ? $c * ($this->gregorianCount / $splitTotal) : 0;

        return '<!DOCTYPE html><html><head><meta name="viewport" content="width=112,initial-scale=1"><style>html,body{margin:0;padding:0;background:transparent;overflow:hidden}svg{display:block}</style></head><body>'
            .'<svg width="112" height="112" viewBox="0 0 112 112">'
            .'<circle cx="56" cy="56" r="'.$r.'" fill="none" stroke="'.theme('progress-track').'" stroke-width="16"/>'
            .'<circle cx="56" cy="56" r="'.$r.'" fill="none" stroke="'.theme('primary').'" stroke-width="16" stroke-dasharray="'.$gregLen.' '.$c.'" transform="rotate(-90 56 56)"/>'
            .'<circle cx="56" cy="56" r="'.$r.'" fill="none" stroke="'.theme('accent').'" stroke-width="16" stroke-dasharray="'.($c - $gregLen).' '.$c.'" stroke-dashoffset="'.(-$gregLen).'" transform="rotate(-90 56 56)"/>'
            .'<text x="56" y="57" text-anchor="middle" dominant-baseline="central" font-size="20" font-weight="700" fill="'.theme('on-surface').'" font-family="system-ui,sans-serif">'.$splitTotal.'</text>'
            .'</svg></body></html>';
    }

    public function render(): View
    {
        return view('native.dashboard');
    }
}
