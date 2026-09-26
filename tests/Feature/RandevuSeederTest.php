<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Services\RandevuHijri;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RandevuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RandevuSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_produces_upcoming_memories_and_both_calendars(): void
    {
        (new RandevuSeeder)->run();

        // 6 gregorian rows + 4 hijri occasions.
        $this->assertSame(10, Randevu::count());

        // Tolerant bounds: on the rare day a Hijri occasion falls exactly on
        // today, next() resolves it to today and upcoming/today shift by one.
        $upcoming = Randevu::upcoming()->count();
        $this->assertGreaterThanOrEqual(6, $upcoming);
        $this->assertGreaterThanOrEqual(1, Randevu::today()->count());
        $this->assertSame(10 - $upcoming, Randevu::memories()->count());

        $this->assertSame(6, Randevu::where('entered_in', 'gregorian')->count());
        $this->assertSame(4, Randevu::where('entered_in', 'hijri')->count());

        foreach (Randevu::all() as $randevu) {
            $this->assertTrue(
                RandevuHijri::valid(
                    (int) $randevu->hijri_year,
                    (int) $randevu->hijri_month,
                    (int) $randevu->hijri_day
                ),
                "Invalid Hijri triple on [{$randevu->title}]."
            );

            $this->assertTrue(
                Randevu::hasAnyUnit($randevu->show_years, $randevu->show_months, $randevu->show_days),
                "At least one distance unit must stay on [{$randevu->title}]."
            );

            $this->assertContains($randevu->color, array_values(Randevu::COLOR_PRESETS));
        }
    }

    public function test_seeder_is_rerunnable(): void
    {
        (new RandevuSeeder)->run();
        (new RandevuSeeder)->run();

        $this->assertSame(10, Randevu::count());
    }

    public function test_database_seeder_still_seeds_nothing(): void
    {
        (new DatabaseSeeder)->run();

        // End-user devices boot with an empty database: the default seeder
        // must never create demo rows. Dev content runs only via
        // `php artisan db:seed --class=RandevuSeeder` (local only).
        $this->assertSame(0, Randevu::count());
    }
}
