<?php

namespace Tests\Unit;

use App\Models\Randevu;
use App\Services\RandevuHijri;
use Tests\TestCase;

class RandevuTest extends TestCase
{
    public function test_next_year_dates_shifts_gregorian(): void
    {
        $randevu = new Randevu(['occurs_on' => '2026-11-08', 'entered_in' => 'gregorian']);

        $this->assertSame(
            array_merge(['occurs_on' => '2027-11-08', 'entered_in' => 'gregorian'], Randevu::hijriTriple('2027-11-08')),
            $randevu->nextYearDates()
        );
    }

    public function test_next_year_dates_clamps_feb_29(): void
    {
        $randevu = new Randevu(['occurs_on' => '2024-02-29', 'entered_in' => 'gregorian']);

        $shifted = $randevu->nextYearDates();

        $this->assertSame('2025-02-28', $shifted['occurs_on']);
        $this->assertSame('gregorian', $shifted['entered_in']);
    }

    public function test_next_year_dates_shifts_hijri_year(): void
    {
        [$gy, $gm, $gd] = RandevuHijri::toGregorian(1448, 5, 12);
        $randevu = new Randevu([
            'occurs_on' => sprintf('%04d-%02d-%02d', $gy, $gm, $gd),
            'hijri_year' => 1448,
            'hijri_month' => 5,
            'hijri_day' => 12,
            'entered_in' => 'hijri',
        ]);

        [$ey, $em, $ed] = RandevuHijri::toGregorian(1449, 5, 12);

        $this->assertSame([
            'occurs_on' => sprintf('%04d-%02d-%02d', $ey, $em, $ed),
            'hijri_year' => 1449,
            'hijri_month' => 5,
            'hijri_day' => 12,
            'entered_in' => 'hijri',
        ], $randevu->nextYearDates());
    }

    public function test_next_year_dates_clamps_long_hijri_month(): void
    {
        [$gy, $gm, $gd] = RandevuHijri::toGregorian(1447, 12, 30);
        $randevu = new Randevu([
            'occurs_on' => sprintf('%04d-%02d-%02d', $gy, $gm, $gd),
            'hijri_year' => 1447,
            'hijri_month' => 12,
            'hijri_day' => 30,
            'entered_in' => 'hijri',
        ]);

        [$ey, $em, $ed] = RandevuHijri::toGregorian(1448, 12, 29);

        $shifted = $randevu->nextYearDates();

        $this->assertSame(1448, $shifted['hijri_year']);
        $this->assertSame(29, $shifted['hijri_day']);
        $this->assertSame(sprintf('%04d-%02d-%02d', $ey, $em, $ed), $shifted['occurs_on']);
    }

    public function test_next_year_dates_falls_back_to_gregorian_without_triple(): void
    {
        $randevu = new Randevu(['occurs_on' => '2026-11-08', 'entered_in' => 'hijri']);

        $shifted = $randevu->nextYearDates();

        $this->assertSame('2027-11-08', $shifted['occurs_on']);
        $this->assertSame('hijri', $shifted['entered_in']);
    }
}
