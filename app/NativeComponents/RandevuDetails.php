<?php

namespace App\NativeComponents;

use App\Models\Randevu;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use App\Services\CoverImage;
use App\Services\RandevuTime;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

/**
 * Read-only full view of one randevu. Cards link here; editing stays on
 * the edit screen (linked from here).
 */
class RandevuDetails extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;

    public int $randevuId = 0;

    /** @var array<string,mixed>|null */
    public ?array $randevu = null;

    /** Full-size cover viewer overlay (opened by tapping the card image). */
    public bool $show_full_cover = false;

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $this->randevuId = (int) $this->param('id');
        $this->refresh();
    }

    public function navTitle(): string
    {
        return __('randevu.details_title');
    }

    /** @press entry point — bare method only. */
    public function openCover(): void
    {
        $this->show_full_cover = true;
    }

    /** @press entry point — bare method only. */
    public function closeCover(): void
    {
        $this->show_full_cover = false;
    }

    public function refresh(): void
    {
        $randevu = Randevu::find($this->randevuId);

        if ($randevu === null) {
            $this->randevu = null;

            return;
        }

        $this->randevu = [
            'id' => $randevu->id,
            'title' => $randevu->title,
            'occurs_on' => $randevu->occurs_on->toDateString(),
            'absolute' => RandevuTime::absolute($randevu->occurs_on),
            'hijri' => $randevu->hijriLabel(),
            'phrase' => $randevu->relativePhrase(),
            'exact' => RandevuTime::exactSuffix($randevu->exactDayCount()),
            'is_appointment' => $randevu->isAppointment(),
            'is_today' => $randevu->isToday(),
            'note' => $randevu->note,
            'cover' => CoverImage::src($randevu->cover_path),
        ];
    }

    public function render(): View
    {
        return view('native.randevu-details');
    }
}
