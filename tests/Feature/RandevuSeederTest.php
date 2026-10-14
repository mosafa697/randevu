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

        // 13 gregorian rows + 4 hijri occasions.
        $this->assertSame(17, Randevu::count());

        // Tolerant bounds: on the rare day a Hijri occasion falls exactly on
        // today, next() resolves it to today and upcoming/today shift by one.
        $upcoming = Randevu::upcoming()->count();
        $this->assertGreaterThanOrEqual(12, $upcoming);
        $this->assertGreaterThanOrEqual(1, Randevu::today()->count());
        $this->assertSame(17 - $upcoming, Randevu::memories()->count());

        $this->assertSame(13, Randevu::where('entered_in', 'gregorian')->count());
        $this->assertSame(4, Randevu::where('entered_in', 'hijri')->count());

        $presets = array_values(Randevu::COLOR_PRESETS);

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
                Randevu::hasAnyUnit($randevu->show_years, $randevu->show_months, $randevu->show_days, $randevu->show_hours),
                "At least one distance unit must stay on [{$randevu->title}]."
            );

            // Preset palette, one null (image-only card), one custom hex.
            $this->assertTrue(
                $randevu->color === null
                    || in_array($randevu->color, $presets, true)
                    || (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $randevu->color),
                "Unexpected color on [{$randevu->title}]."
            );

            if ($randevu->cover_path !== null) {
                $this->assertFileExists(storage_path('app/'.$randevu->cover_path));
                $this->assertSame('image/png', mime_content_type(storage_path('app/'.$randevu->cover_path)));
            }
        }

        // Every preset appears at least once.
        $used = Randevu::whereNotNull('color')->pluck('color')->all();
        foreach ($presets as $hex) {
            $this->assertContains($hex, $used);
        }

        // Null color, custom color, and null notes are covered.
        $this->assertSame(1, Randevu::whereNull('color')->count());
        $this->assertSame(1, Randevu::whereNotNull('color')->whereNotIn('color', $presets)->count());
        $this->assertGreaterThanOrEqual(1, Randevu::whereNull('note')->count());

        // Demo content spans all three locales (Arabic script vs Latin).
        $titles = Randevu::pluck('title')->all();
        $arabic = array_filter($titles, fn ($t) => (bool) preg_match('/\p{Arabic}/u', $t));
        $this->assertGreaterThanOrEqual(1, count($arabic));
        $this->assertGreaterThanOrEqual(1, count($titles) - count($arabic));

        // Egyptian vs MSA share the Arabic script: pin dialect markers.
        $this->assertContains('بكرة — عيد ميلاد ماما', $titles);
        $this->assertContains('اجتماع مجلس الإدارة', $titles);

        // Time-of-day rows drive the hours unit.
        $this->assertGreaterThanOrEqual(
            2,
            Randevu::whereNotNull('occurs_time')->where('show_hours', true)->count()
        );
    }

    public function test_seeder_is_rerunnable(): void
    {
        (new RandevuSeeder)->run();
        (new RandevuSeeder)->run();

        $this->assertSame(17, Randevu::count());
    }

    public function test_database_seeder_still_seeds_nothing(): void
    {
        (new DatabaseSeeder)->run();

        // End-user devices boot with an empty database: the default seeder
        // must never create demo rows. Dev content runs only via
        // `php artisan db:seed --class=RandevuSeeder` (local only).
        $this->assertSame(0, Randevu::count());
    }

    protected function tearDown(): void
    {
        // Demo covers only — user files in covers/ are never touched.
        foreach (glob(storage_path('app/covers/demo-*.*')) ?: [] as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }
}
