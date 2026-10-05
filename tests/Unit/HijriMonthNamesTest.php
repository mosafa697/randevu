<?php

namespace Tests\Unit;

use App\Services\RandevuHijri;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class HijriMonthNamesTest extends TestCase
{
    public function test_english_month_names_in_order(): void
    {
        App::setLocale('en');

        $this->assertSame(
            [
                'Muharram', 'Safar', "Rabi' al-Awwal", "Rabi' al-Thani",
                'Jumada al-Awwal', 'Jumada al-Thani', 'Rajab', "Sha'ban",
                'Ramadan', 'Shawwal', "Dhu al-Qi'dah", "Dhu al-Hijjah",
            ],
            RandevuHijri::monthNames()
        );
    }

    public function test_arabic_month_names_by_default(): void
    {
        App::setLocale('ar');

        $this->assertSame(RandevuHijri::MONTH_NAMES, RandevuHijri::monthNames());
    }

    public function test_format_uses_current_language_with_western_digits(): void
    {
        App::setLocale('en');
        $this->assertSame("12 Rabi' al-Thani 1448", RandevuHijri::format(1448, 4, 12));
        $this->assertSame('1 Ramadan 1447', RandevuHijri::format(1447, 9, 1));

        App::setLocale('ar');
        $this->assertSame('12 ربيع الثاني 1448', RandevuHijri::format(1448, 4, 12));
    }

    public function test_month_number_resolves_both_languages(): void
    {
        App::setLocale('en');
        $this->assertSame(9, RandevuHijri::monthNumber('Ramadan'));
        // Arabic fallback still resolves in English mode (stale selections).
        $this->assertSame(9, RandevuHijri::monthNumber('رمضان'));

        App::setLocale('ar');
        $this->assertSame(4, RandevuHijri::monthNumber('ربيع الثاني'));
        $this->assertNull(RandevuHijri::monthNumber('Not a month'));
    }

    public function test_era_suffix_per_language(): void
    {
        App::setLocale('en');
        $this->assertSame('AH', RandevuHijri::hijriSuffix());

        App::setLocale('ar');
        $this->assertSame('هـ', RandevuHijri::hijriSuffix());
    }
}
