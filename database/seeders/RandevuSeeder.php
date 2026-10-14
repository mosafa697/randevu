<?php

namespace Database\Seeders;

use App\Models\Randevu;
use App\Services\RandevuHijri;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Dev-only demo content for Jump / manual QA. NEVER call this from
 * DatabaseSeeder::run() — per-device SQLite holds user data, so end-user
 * devices must always boot with an empty database.
 *
 * Local use only:
 *   php artisan migrate:fresh --seed --seeder=RandevuSeeder
 *   php artisan db:seed --class=RandevuSeeder
 *
 * Coverage matrix (13 Gregorian + 4 Hijri-entered = 17 rows):
 * - Languages: titles/notes cycle en → ar (Egyptian) → ar_fusha (MSA), so
 *   every locale shows native demo content when switching in Settings.
 * - Dates: today, tomorrow, yesterday, near/far future, recent/old past.
 * - Time-of-day: rows with occurs_time + show_hours (hours-only, days+hours).
 * - Units: all-on, each single unit alone, and combined subsets.
 * - Colors: every COLOR_PRESETS entry, plus one null (image-only card) and
 *   one custom hex outside the presets.
 * - Covers: GD-generated demo PNGs under storage/app/covers/demo-*.png
 *   (deleted + regenerated on rerun; user files are never touched).
 * - Notes: present on most rows, null on two (optional-field coverage).
 */
class RandevuSeeder extends Seeder
{
    public function run(): void
    {
        // Covers are GD-generated: fail fast with a clear message BEFORE
        // wiping anything, so a GD-less machine never half-seeds.
        if (! extension_loaded('gd')) {
            throw new RuntimeException('RandevuSeeder covers need ext-gd.');
        }

        // Refresh-safe: rerunning the seeder restores the same demo set.
        Randevu::query()->delete();
        $this->clearDemoCovers();

        $today = Carbon::today();

        foreach ($this->gregorianRows($today) as $row) {
            Randevu::create(array_merge($row, Randevu::hijriTriple($row['occurs_on'])));
        }

        // --- Hijri-entered Islamic occasions (resolved to Gregorian via
        // the tabular calendar; Gregorian stays the source of truth). ---
        foreach ($this->hijriOccasions($today) as $occasion) {
            [$gy, $gm, $gd] = RandevuHijri::toGregorian(
                $occasion['hijri_year'], $occasion['hijri_month'], $occasion['hijri_day']
            );

            Randevu::create([
                'title' => $occasion['title'],
                'note' => $occasion['note'],
                'occurs_on' => sprintf('%04d-%02d-%02d', $gy, $gm, $gd),
                'occurs_time' => $occasion['occurs_time'],
                'entered_in' => 'hijri',
                'hijri_year' => $occasion['hijri_year'],
                'hijri_month' => $occasion['hijri_month'],
                'hijri_day' => $occasion['hijri_day'],
                'color' => $occasion['color'],
                'cover_path' => $occasion['cover'],
                'show_years' => $occasion['show_years'],
                'show_months' => $occasion['show_months'],
                'show_days' => $occasion['show_days'],
                'show_hours' => $occasion['show_hours'],
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function gregorianRows(Carbon $today): array
    {
        $at = fn (int $days): string => $today->copy()->addDays($days)->toDateString();
        $preset = Randevu::COLOR_PRESETS;

        return [
            // Today badge, all units, preset color + cover (EN).
            [
                'title' => 'Today — dentist checkup',
                'note' => 'Demo: today badge (appointment).',
                'occurs_on' => $at(0),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['blue'],
                'cover_path' => $this->demoCover('today-dentist', $preset['blue']),
                'show_years' => true, 'show_months' => true, 'show_days' => true, 'show_hours' => false,
            ],
            // Tomorrow with a time, days + hours (AR Egyptian).
            [
                'title' => 'بكرة — عيد ميلاد ماما',
                'note' => 'الهدية والتورتة… متنساش تكلم أخوك.',
                'occurs_on' => $at(1),
                'occurs_time' => '14:30',
                'entered_in' => 'gregorian',
                'color' => $preset['indigo'],
                'cover_path' => $this->demoCover('mama-birthday', $preset['indigo']),
                'show_years' => false, 'show_months' => false, 'show_days' => true, 'show_hours' => true,
            ],
            // Hours-only with a morning time (MSA).
            [
                'title' => 'اجتماع مجلس الإدارة',
                'note' => 'القاعة الكبرى، الطابق العاشر.',
                'occurs_on' => $at(2),
                'occurs_time' => '09:15',
                'entered_in' => 'gregorian',
                'color' => $preset['teal'],
                'cover_path' => $this->demoCover('board-meeting', $preset['teal']),
                'show_years' => false, 'show_months' => false, 'show_days' => false, 'show_hours' => true,
            ],
            // Days-only, no cover (EN).
            [
                'title' => 'Flight to Cairo',
                'note' => 'Terminal 3, booking ref lives in email.',
                'occurs_on' => $at(6),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['green'],
                'cover_path' => null,
                'show_years' => false, 'show_months' => false, 'show_days' => true, 'show_hours' => false,
            ],
            // Months-only (AR Egyptian).
            [
                'title' => 'فرح بنت خالتي',
                'note' => 'القاعة على النيل… ظبط البدلة قبلها بأسبوع.',
                'occurs_on' => $at(30),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['orange'],
                'cover_path' => null,
                'show_years' => false, 'show_months' => true, 'show_days' => false, 'show_hours' => false,
            ],
            // Months + days combined, with cover (MSA).
            [
                'title' => 'مؤتمر القاهرة للتقنية',
                'note' => 'التذكرة على الإيميل، والفندق قريب من القاعة.',
                'occurs_on' => $at(75),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['purple'],
                'cover_path' => $this->demoCover('tech-conference', $preset['purple']),
                'show_years' => false, 'show_months' => true, 'show_days' => true, 'show_hours' => false,
            ],
            // Years + months combined (EN).
            [
                'title' => 'Graduation anniversary trip',
                'note' => 'Same hostel, same friends, ten years later.',
                'occurs_on' => $at(200),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['pink'],
                'cover_path' => null,
                'show_years' => true, 'show_months' => true, 'show_days' => false, 'show_hours' => false,
            ],
            // Years-only far future, null note (AR Egyptian).
            [
                'title' => 'رحلة العمرة',
                'note' => null,
                'occurs_on' => $at(400),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['red'],
                'cover_path' => $this->demoCover('umrah-trip', $preset['red']),
                'show_years' => true, 'show_months' => false, 'show_days' => false, 'show_hours' => false,
            ],
            // Yesterday memory with an evening time, days + hours (MSA).
            [
                'title' => 'حفل توقيع الكتاب',
                'note' => 'اشتريت نسختين، واحدة مهداة.',
                'occurs_on' => $at(-1),
                'occurs_time' => '18:45',
                'entered_in' => 'gregorian',
                'color' => $preset['amber'],
                'cover_path' => $this->demoCover('book-signing', $preset['amber']),
                'show_years' => false, 'show_months' => false, 'show_days' => true, 'show_hours' => true,
            ],
            // Recent memory, days-only (EN).
            [
                'title' => 'Weekend picnic',
                'note' => 'Demo: recent memory.',
                'occurs_on' => $at(-5),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['cyan'],
                'cover_path' => null,
                'show_years' => false, 'show_months' => false, 'show_days' => true, 'show_hours' => false,
            ],
            // Old memory, exact calendar breakdown (AR Egyptian).
            [
                'title' => 'سفرية الشتا',
                'note' => 'يومين في السخنة… الجو كان تحفة.',
                'occurs_on' => $at(-200),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => $preset['brown'],
                'cover_path' => null,
                'show_years' => true, 'show_months' => true, 'show_days' => true, 'show_hours' => false,
            ],
            // Null color + cover: image-only card (MSA).
            [
                'title' => 'غروب على الكورنيش',
                'note' => 'صورة من غير لون مخصص — البطاقة بالغلاف بس.',
                'occurs_on' => $at(12),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => null,
                'cover_path' => $this->demoCover('corniche-sunset', '#F59E0B'),
                'show_years' => false, 'show_months' => false, 'show_days' => true, 'show_hours' => false,
            ],
            // Custom hex outside the presets, null note (EN).
            [
                'title' => 'Suit fitting',
                'note' => null,
                'occurs_on' => $at(3),
                'occurs_time' => null,
                'entered_in' => 'gregorian',
                'color' => '#A3E635',
                'cover_path' => null,
                'show_years' => false, 'show_months' => false, 'show_days' => true, 'show_hours' => false,
            ],
        ];
    }

    /**
     * Two future + two past Hijri-entered occasions, anchored to the Hijri
     * year containing today so scopes stay correct whenever seeded.
     *
     * @return list<array{title: string, note: ?string, occurs_time: ?string, hijri_year: int, hijri_month: int, hijri_day: int, color: ?string, cover: ?string, show_years: bool, show_months: bool, show_days: bool, show_hours: bool}>
     */
    private function hijriOccasions(Carbon $today): array
    {
        $preset = Randevu::COLOR_PRESETS;

        [$hijriYear] = RandevuHijri::fromGregorian($today->year, $today->month, $today->day);

        $future = fn (int $month, int $day, int $yearOffset = 0): array => RandevuHijri::toGregorian(
            $hijriYear + $yearOffset, $month, $day
        );

        $isFuture = fn (array $g, string $ref): bool => sprintf('%04d-%02d-%02d', ...$g) >= $ref;
        $ref = $today->toDateString();

        // Next occurrence (>= today) for future rows.
        $next = function (int $month, int $day) use ($future, $isFuture, $ref, $hijriYear): int {
            $g = $future($month, $day);
            if ($isFuture($g, $ref)) {
                return $hijriYear;
            }

            return $hijriYear + 1;
        };

        // Previous occurrence (< today) for memory rows.
        $prev = function (int $month, int $day) use ($future, $isFuture, $ref, $hijriYear): int {
            $g = $future($month, $day);
            if (! $isFuture($g, $ref)) {
                return $hijriYear;
            }

            return $hijriYear - 1;
        };

        return [
            [
                'title' => 'هلال رمضان — أول الشهر',
                'note' => 'مدخل بالهجري: ميعاد جاي.',
                'occurs_time' => null,
                'hijri_year' => $next(9, 1),
                'hijri_month' => 9,
                'hijri_day' => 1,
                'color' => $preset['green'],
                'cover' => $this->demoCover('ramadan-moon', $preset['green']),
                'show_years' => false,
                'show_months' => true,
                'show_days' => true,
                'show_hours' => false,
            ],
            [
                'title' => 'العيد — أول يوم',
                'note' => 'مدخل بالهجري: صلاة العيد وبعدها الغدا عند ماما.',
                'occurs_time' => '08:00',
                'hijri_year' => $next(10, 1),
                'hijri_month' => 10,
                'hijri_day' => 1,
                'color' => $preset['gray'],
                'cover' => null,
                'show_years' => false,
                'show_months' => false,
                'show_days' => true,
                'show_hours' => true,
            ],
            [
                'title' => 'Eid al-Adha barbecue',
                'note' => 'Demo: past Hijri memory.',
                'occurs_time' => null,
                'hijri_year' => $prev(12, 10),
                'hijri_month' => 12,
                'hijri_day' => 10,
                'color' => $preset['amber'],
                'cover' => $this->demoCover('adha-barbecue', $preset['amber']),
                'show_years' => true,
                'show_months' => true,
                'show_days' => true,
                'show_hours' => false,
            ],
            [
                'title' => 'راس السنة الهجرية',
                'note' => 'مدخل بالهجري: ذكرى فاتت.',
                'occurs_time' => null,
                'hijri_year' => $prev(1, 1),
                'hijri_month' => 1,
                'hijri_day' => 1,
                'color' => $preset['teal'],
                'cover' => null,
                'show_years' => true,
                'show_months' => false,
                'show_days' => false,
                'show_hours' => false,
            ],
        ];
    }

    /**
     * Generate a small demo cover (gradient + soft circles) tinted with the
     * row's color. Pure GD — run() aborts early when ext-gd is missing, so
     * reaching here guarantees the functions below exist. Returns the
     * relative `covers/demo-<slug>.png` ref stored in cover_path.
     */
    private function demoCover(string $slug, string $hex): string
    {
        // Covers live at storage/app/covers (CoverImage convention), NOT on
        // the 'local' disk (rooted at storage/app/private since Laravel 11).
        File::ensureDirectoryExists(storage_path('app/covers'));

        [$r, $g, $b] = $this->hexToRgb($hex);

        $width = 960;
        $height = 540;
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            $lift = (int) (90 * ($y / max(1, $height - 1)));
            $color = imagecolorallocate(
                $image,
                min(255, $r + $lift),
                min(255, $g + $lift),
                min(255, $b + $lift)
            );
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Soft translucent circles for a gallery-like feel.
        $white = imagecolorallocatealpha($image, 255, 255, 255, 80);
        imagefilledellipse($image, (int) ($width * 0.78), (int) ($height * 0.30), 300, 300, $white);
        imagefilledellipse($image, (int) ($width * 0.18), (int) ($height * 0.78), 200, 200, $white);

        $path = storage_path('app/covers/demo-'.$slug.'.png');

        if (! imagepng($image, $path)) {
            imagedestroy($image);

            throw new RuntimeException("Could not write demo cover [{$path}].");
        }

        imagedestroy($image);

        return 'covers/demo-'.$slug.'.png';
    }

    /** @return array{0: int, 1: int, 2: int} */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /** Remove only seeder-owned files; user covers are never touched. */
    private function clearDemoCovers(): void
    {
        foreach ((array) glob(storage_path('app/covers/demo-*.*')) as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
