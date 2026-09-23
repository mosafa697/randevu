<?php

namespace Tests\Unit;

use App\Services\RandevuHijri;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class RandevuHijriTest extends TestCase
{
    public function test_tabular_anchor_is_pinned(): void
    {
        // Tabular (civil) calendar, not observed Umm al-Qura — pins the
        // algorithm against regression, not against moon sighting.
        $this->assertSame([1448, 4, 10], RandevuHijri::fromGregorian(2026, 9, 23));
        $this->assertSame([2026, 9, 23], RandevuHijri::toGregorian(1448, 4, 10));
    }

    public function test_round_trip_over_two_centuries(): void
    {
        $date = new DateTimeImmutable('1900-01-01');
        $checked = 0;

        while ($date->format('Y') < 2100) {
            $g = [(int) $date->format('Y'), (int) $date->format('m'), (int) $date->format('d')];
            $this->assertSame($g, RandevuHijri::toGregorian(...RandevuHijri::fromGregorian(...$g)));
            $date = $date->modify('+40 days');
            $checked++;
        }

        $this->assertGreaterThan(1500, $checked);
    }

    public function test_month_lengths_and_leap_rule(): void
    {
        // Odd months 30 days, even months 29, except Dhu al-Hijjah.
        $this->assertSame(30, RandevuHijri::daysInMonth(1448, 1));
        $this->assertSame(29, RandevuHijri::daysInMonth(1448, 2));
        $this->assertSame(30, RandevuHijri::daysInMonth(1448, 9));
        // 1447 is leap (position 7 of the 30-year cycle), 1448 is not.
        $this->assertTrue(RandevuHijri::isLeapYear(1447));
        $this->assertFalse(RandevuHijri::isLeapYear(1448));
        $this->assertSame(29, RandevuHijri::daysInMonth(1448, 12));
        $this->assertSame(30, RandevuHijri::daysInMonth(1447, 12));
    }

    public function test_invalid_combos_rejected(): void
    {
        $this->assertFalse(RandevuHijri::valid(1448, 2, 30)); // Safar has 29
        $this->assertFalse(RandevuHijri::valid(1448, 12, 30)); // non-leap year
        $this->assertTrue(RandevuHijri::valid(1447, 12, 30)); // leap year
        $this->assertFalse(RandevuHijri::valid(1448, 13, 1));
        $this->assertFalse(RandevuHijri::valid(1448, 0, 1));
        $this->assertNull(RandevuHijri::monthNumber('Not a month'));
        $this->assertSame(9, RandevuHijri::monthNumber('رمضان'));
    }

    public function test_format_label(): void
    {
        $this->assertSame('12 ربيع الثاني 1448', RandevuHijri::format(1448, 4, 12));
        $this->assertSame('1 رمضان 1447', RandevuHijri::format(1447, 9, 1));
    }
}
