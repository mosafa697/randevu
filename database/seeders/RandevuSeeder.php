<?php

namespace Database\Seeders;

use App\Models\Randevu;
use App\Services\RandevuHijri;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Dev-only demo content for Jump / manual QA. NEVER call this from
 * DatabaseSeeder::run() — per-device SQLite holds user data, so end-user
 * devices must always boot with an empty database.
 *
 * Local use only:
 *   php artisan migrate:fresh --seed --seeder=RandevuSeeder
 *   php artisan db:seed --class=RandevuSeeder
 */
class RandevuSeeder extends Seeder
{
    public function run(): void
    {
        // Refresh-safe: rerunning the seeder restores the same demo set.
        Randevu::query()->delete();

        $colors = array_values(Randevu::COLOR_PRESETS);
        $pick = static fn (int $i): ?string => $colors[$i % count($colors)];

        $today = Carbon::today();

        $rows = [
            // --- Upcoming (includes today) ---
            [
                'title' => 'Today — dentist checkup',
                'note' => 'Demo: today badge (appointment).',
                'occurs_on' => $today->toDateString(),
                'entered_in' => 'gregorian',
                'color' => $pick(0),
                'show_years' => true, 'show_months' => true, 'show_days' => true,
            ],
            [
                'title' => 'Flight to Cairo',
                'note' => 'Demo: days-only distance.',
                'occurs_on' => $today->copy()->addDays(6)->toDateString(),
                'entered_in' => 'gregorian',
                'color' => $pick(7),
                'show_years' => false, 'show_months' => false, 'show_days' => true,
            ],
            [
                'title' => 'Cousin wedding',
                'note' => 'Demo: months distance.',
                'occurs_on' => $today->copy()->addDays(75)->toDateString(),
                'entered_in' => 'gregorian',
                'color' => $pick(3),
                'show_years' => false, 'show_months' => true, 'show_days' => false,
            ],
            [
                'title' => 'Graduation anniversary trip',
                'note' => 'Demo: years distance.',
                'occurs_on' => $today->copy()->addDays(400)->toDateString(),
                'entered_in' => 'gregorian',
                'color' => $pick(1),
                'show_years' => true, 'show_months' => false, 'show_days' => false,
            ],

            // --- Memories ---
            [
                'title' => 'Weekend picnic',
                'note' => 'Demo: recent memory.',
                'occurs_on' => $today->copy()->subDays(5)->toDateString(),
                'entered_in' => 'gregorian',
                'color' => $pick(9),
                'show_years' => false, 'show_months' => false, 'show_days' => true,
            ],
            [
                'title' => 'Winter road trip',
                'note' => 'Demo: older memory, combined units.',
                'occurs_on' => $today->copy()->subDays(200)->toDateString(),
                'entered_in' => 'gregorian',
                'color' => $pick(10),
                'show_years' => true, 'show_months' => true, 'show_days' => true,
            ],
        ];

        foreach ($rows as $row) {
            Randevu::create(array_merge($row, Randevu::hijriTriple($row['occurs_on'])));
        }

        // --- Hijri-entered Islamic occasions (resolved to Gregorian via
        // the tabular calendar; Gregorian stays the source of truth). ---
        foreach ($this->hijriOccasions($today) as $i => $occasion) {
            [$gy, $gm, $gd] = RandevuHijri::toGregorian(
                $occasion['hijri_year'], $occasion['hijri_month'], $occasion['hijri_day']
            );

            Randevu::create([
                'title' => $occasion['title'],
                'note' => $occasion['note'],
                'occurs_on' => sprintf('%04d-%02d-%02d', $gy, $gm, $gd),
                'entered_in' => 'hijri',
                'hijri_year' => $occasion['hijri_year'],
                'hijri_month' => $occasion['hijri_month'],
                'hijri_day' => $occasion['hijri_day'],
                'color' => $pick($i + 2),
                'show_years' => (bool) ($i % 2),
                'show_months' => true,
                'show_days' => true,
            ]);
        }
    }

    /**
     * Two future + two past Hijri-entered occasions, anchored to the Hijri
     * year containing today so scopes stay correct whenever seeded.
     *
     * @return list<array{title: string, note: string, hijri_year: int, hijri_month: int, hijri_day: int}>
     */
    private function hijriOccasions(Carbon $today): array
    {
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
                'title' => 'Ramadan 1 (Hijri-entered)',
                'note' => 'Demo: future Hijri appointment.',
                'hijri_year' => $next(9, 1),
                'hijri_month' => 9,
                'hijri_day' => 1,
            ],
            [
                'title' => 'Eid al-Fitr — 1 Shawwal (Hijri-entered)',
                'note' => 'Demo: future Hijri appointment.',
                'hijri_year' => $next(10, 1),
                'hijri_month' => 10,
                'hijri_day' => 1,
            ],
            [
                'title' => 'Eid al-Adha — 10 Dhu al-Hijjah (Hijri-entered)',
                'note' => 'Demo: past Hijri memory.',
                'hijri_year' => $prev(12, 10),
                'hijri_month' => 12,
                'hijri_day' => 10,
            ],
            [
                'title' => 'Islamic New Year — 1 Muharram (Hijri-entered)',
                'note' => 'Demo: past Hijri memory.',
                'hijri_year' => $prev(1, 1),
                'hijri_month' => 1,
                'hijri_day' => 1,
            ],
        ];
    }
}
