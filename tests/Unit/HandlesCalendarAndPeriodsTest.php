<?php

namespace Tests\Unit;

use App\NativeComponents\RandevuCreate;
use App\Services\RandevuHijri;
use App\Services\RandevuTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class HandlesCalendarAndPeriodsTest extends TestCase
{
    public function test_gregorian_max_day(): void
    {
        // Month names resolve through the active locale.
        App::setLocale('en');

        $this->assertSame(28, RandevuCreate::gregorianMaxDay('2026', 'February'));
        $this->assertSame(29, RandevuCreate::gregorianMaxDay('2024', 'February'));
        $this->assertSame(30, RandevuCreate::gregorianMaxDay('2026', 'April'));
        $this->assertSame(30, RandevuCreate::gregorianMaxDay('2026', 'June'));
        $this->assertSame(31, RandevuCreate::gregorianMaxDay('2026', 'January'));
        $this->assertSame(31, RandevuCreate::gregorianMaxDay('2026', 'December'));
        $this->assertSame(31, RandevuCreate::gregorianMaxDay('2026', 'Nope'));
        $this->assertSame(31, RandevuCreate::gregorianMaxDay('', ''));
    }

    public function test_hijri_max_day(): void
    {
        $this->assertSame(30, RandevuCreate::hijriMaxDay('1448', 'محرم'));
        $this->assertSame(29, RandevuCreate::hijriMaxDay('1448', 'صفر'));
        $this->assertSame(30, RandevuCreate::hijriMaxDay('1447', 'ذو الحجة'));
        $this->assertSame(29, RandevuCreate::hijriMaxDay('1448', 'ذو الحجة'));
        $this->assertSame(30, RandevuCreate::hijriMaxDay('1448', 'Nope'));
        $this->assertSame(30, RandevuCreate::hijriMaxDay('', ''));
    }

    public function test_quick_set_chips_write_both_calendars(): void
    {
        App::setLocale('en');

        $form = new RandevuCreate;
        $form->calendar_mode = 'gregorian';
        $form->setPlus30();

        $date = today()->addDays(30);
        $this->assertSame((string) $date->day, $form->day);
        $this->assertSame(RandevuTime::monthNames()[$date->month - 1], $form->month);
        $this->assertSame((string) $date->year, $form->year);

        [$hy, $hm, $hd] = RandevuHijri::fromGregorian($date->year, $date->month, $date->day);
        $this->assertSame((string) $hd, $form->h_day);
        $this->assertSame(RandevuHijri::MONTH_NAMES[$hm - 1], $form->h_month);
        $this->assertSame((string) $hy, $form->h_year);
    }

    public function test_quick_set_chips_roll_over_month_and_year(): void
    {
        App::setLocale('en');

        $this->travelTo(Carbon::parse('2026-12-20'), function (): void {
            $form = new RandevuCreate;
            $form->calendar_mode = 'hijri';
            $form->setPlus30();

            // Dec 20 + 30 days = Jan 19 next year.
            $this->assertSame('19', $form->day);
            $this->assertSame('January', $form->month);
            $this->assertSame('2027', $form->year);

            [$hy, $hm, $hd] = RandevuHijri::fromGregorian(2027, 1, 19);
            $this->assertSame((string) $hd, $form->h_day);
            $this->assertSame(RandevuHijri::MONTH_NAMES[$hm - 1], $form->h_month);
            $this->assertSame((string) $hy, $form->h_year);

            $form->setToday();
            $this->assertSame('20', $form->day);
            $this->assertSame('December', $form->month);
            $this->assertSame('2026', $form->year);
        });
    }

    public function test_quick_set_chips_refresh_day_options(): void
    {
        App::setLocale('en');

        $this->travelTo(Carbon::parse('2026-02-10'), function (): void {
            $form = new RandevuCreate;
            // February-narrowed options, as after picking February.
            $form->dayOptions = array_map(strval(...), range(1, 28));

            $form->setPlus30();

            // Mar 12: options follow the written month, not the stale one.
            $this->assertSame('12', $form->day);
            $this->assertSame(array_map(strval(...), range(1, 31)), $form->dayOptions);
            $this->assertCount(
                RandevuHijri::daysInMonth((int) $form->h_year, (int) RandevuHijri::monthNumber($form->h_month)),
                $form->hDayOptions
            );
        });
    }
}
