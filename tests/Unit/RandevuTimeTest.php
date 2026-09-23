<?php

namespace Tests\Unit;

use App\Services\RandevuTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class RandevuTimeTest extends TestCase
{
    public function test_today_tomorrow_yesterday_phrases_english(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('Today', RandevuTime::phrase('2026-09-23', $today));
        $this->assertSame('Tomorrow', RandevuTime::phrase('2026-09-24', $today));
        $this->assertSame('Yesterday', RandevuTime::phrase('2026-09-22', $today));
    }

    public function test_days_months_years_phrases_english(): void
    {
        App::setLocale('en');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('In 5 days', RandevuTime::phrase('2026-09-28', $today));
        $this->assertSame('5 days ago', RandevuTime::phrase('2026-09-18', $today));
        $this->assertSame('In 2 months', RandevuTime::phrase('2026-11-23', $today));
        $this->assertSame('2 months ago', RandevuTime::phrase('2026-07-23', $today));
        $this->assertSame('In 1 year', RandevuTime::phrase('2027-09-23', $today));
        $this->assertSame('2 years ago', RandevuTime::phrase('2024-09-23', $today));
    }

    public function test_phrases_arabic_egyptian(): void
    {
        App::setLocale('ar');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('النهاردة', RandevuTime::phrase('2026-09-23', $today));
        $this->assertSame('بكرة', RandevuTime::phrase('2026-09-24', $today));
        $this->assertSame('امبارح', RandevuTime::phrase('2026-09-22', $today));
        $this->assertSame('بعد 5 أيام', RandevuTime::phrase('2026-09-28', $today));
        $this->assertSame('من 5 أيام', RandevuTime::phrase('2026-09-18', $today));
    }

    public function test_arabic_unit_shapes(): void
    {
        App::setLocale('ar');
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('بعد يومين', RandevuTime::phrase('2026-09-25', $today));
        $this->assertSame('من 15 يوم', RandevuTime::phrase('2026-09-08', $today));
        $this->assertSame('بعد شهر', RandevuTime::phrase('2026-10-23', $today));
        $this->assertSame('بعد سنة', RandevuTime::phrase('2027-09-23', $today));
    }

    public function test_month_names_follow_locale(): void
    {
        App::setLocale('en');
        $this->assertSame('September', RandevuTime::monthNames()[8]);

        App::setLocale('ar');
        $this->assertSame('سبتمبر', RandevuTime::monthNames()[8]);
    }

    public function test_absolute_date_follows_locale(): void
    {
        App::setLocale('en');
        $this->assertSame('23 September 2026', RandevuTime::absolute(Carbon::parse('2026-09-23')));

        App::setLocale('ar');
        $this->assertSame('23 سبتمبر 2026', RandevuTime::absolute(Carbon::parse('2026-09-23')));
    }

    public function test_exact_day_count_is_signed(): void
    {
        $today = Carbon::parse('2026-09-23');

        $this->assertSame(0, RandevuTime::dayCount('2026-09-23', $today));
        $this->assertSame(1, RandevuTime::dayCount('2026-09-24', $today));
        $this->assertSame(-1, RandevuTime::dayCount('2026-09-22', $today));
        $this->assertSame(10, RandevuTime::dayCount('2026-10-03', $today));
        $this->assertSame(-10, RandevuTime::dayCount('2026-09-13', $today));
    }
}
