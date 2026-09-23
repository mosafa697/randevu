<?php

namespace Tests\Unit;

use App\Services\RandevuTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class PeriodDisplayTest extends TestCase
{
    public function test_today_tomorrow_yesterday_special_whatever_the_units(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        foreach ([[true, true, true], [true, false, false], [false, true, false], [false, false, true], [true, false, true]] as [$y, $m, $d]) {
            $this->assertSame('Today', RandevuTime::phraseFor('2026-09-23', $y, $m, $d, $today));
            $this->assertSame('Tomorrow', RandevuTime::phraseFor('2026-09-24', $y, $m, $d, $today));
            $this->assertSame('Yesterday', RandevuTime::phraseFor('2026-09-22', $y, $m, $d, $today));
        }
    }

    public function test_days_only_uses_the_exact_day_count(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('In 40 days', RandevuTime::phraseFor('2026-11-02', false, false, true, $today));
        $this->assertSame('40 days ago', RandevuTime::phraseFor('2026-08-14', false, false, true, $today));
        $this->assertSame('In 400 days', RandevuTime::phraseFor('2027-10-28', false, false, true, $today));
    }

    public function test_single_months_or_years_round_like_auto(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('In 1 month', RandevuTime::phraseFor('2026-09-28', false, true, false, $today));
        $this->assertSame('In 2 months', RandevuTime::phraseFor('2026-11-23', false, true, false, $today));
        $this->assertSame('2 months ago', RandevuTime::phraseFor('2026-07-24', false, true, false, $today));
        $this->assertSame('In 1 year', RandevuTime::phraseFor('2026-11-02', true, false, false, $today));
        $this->assertSame('In 1 year', RandevuTime::phraseFor('2027-10-28', true, false, false, $today));

        $farPast = $today->copy()->subDays(800)->toDateString();
        $this->assertSame('2 years ago', RandevuTime::phraseFor($farPast, true, false, false, $today));
    }

    public function test_all_units_give_the_exact_calendar_breakdown(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        // 400 days ahead = 1 year, 1 month, 5 days on the real calendar.
        $this->assertSame(
            'In 1 year, 1 month, 5 days',
            RandevuTime::phraseFor('2027-10-28', true, true, true, $today)
        );
        $this->assertSame('In 5 days', RandevuTime::phraseFor('2026-09-28', true, true, true, $today));
        $this->assertSame('2 months ago', RandevuTime::phraseFor('2026-07-23', true, true, true, $today));
        $this->assertSame('2 years ago', RandevuTime::phraseFor('2024-09-23', true, true, true, $today));
        $this->assertSame(
            '1 year, 1 month, 4 days ago',
            RandevuTime::phraseFor('2025-08-19', true, true, true, $today)
        );
    }

    public function test_skipped_middle_units_roll_down_into_days(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        // 1y 1m 5d with months off: the month rolls into days (1 × 30 + 5).
        $this->assertSame(
            'In 1 year, 35 days',
            RandevuTime::phraseFor('2027-10-28', true, false, true, $today)
        );
        // Years off: 12 nominal months roll into the months part.
        $this->assertSame(
            'In 13 months, 5 days',
            RandevuTime::phraseFor('2027-10-28', false, true, true, $today)
        );
    }

    public function test_switched_off_days_tail_is_dropped(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame(
            'In 1 year, 1 month',
            RandevuTime::phraseFor('2027-10-28', true, true, false, $today)
        );
    }

    public function test_span_smaller_than_smallest_shown_unit_rounds_up_to_one(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('In 1 month', RandevuTime::phraseFor('2026-09-28', true, true, false, $today));
    }

    public function test_no_units_falls_back_to_legacy_phrase(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame(
            RandevuTime::phrase('2026-11-02', $today),
            RandevuTime::phraseFor('2026-11-02', false, false, false, $today)
        );
    }

    public function test_subset_shapes_follow_arabic_egyptian(): void
    {
        App::setLocale('ar');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('بعد 5 أيام', RandevuTime::phraseFor('2026-09-28', false, false, true, $today));
        $this->assertSame('بعد شهر', RandevuTime::phraseFor('2026-10-23', false, true, false, $today));
        $this->assertSame('بعد سنة، شهر، 5 أيام', RandevuTime::phraseFor('2027-10-28', true, true, true, $today));
        $this->assertSame('بعد سنة، 35 يوم', RandevuTime::phraseFor('2027-10-28', true, false, true, $today));
    }
}
