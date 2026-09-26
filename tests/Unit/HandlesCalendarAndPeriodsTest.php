<?php

namespace Tests\Unit;

use App\NativeComponents\RandevuCreate;
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
}
