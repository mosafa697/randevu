<?php

namespace Tests\Unit;

use App\Services\RandevuTime;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class RandevuTimeTest extends TestCase
{
    public function test_today_tomorrow_yesterday_phrases(): void
    {
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('Today', RandevuTime::phrase('2026-09-23', $today));
        $this->assertSame('Tomorrow', RandevuTime::phrase('2026-09-24', $today));
        $this->assertSame('Yesterday', RandevuTime::phrase('2026-09-22', $today));
    }

    public function test_days_months_years_phrases(): void
    {
        $today = Carbon::parse('2026-09-23');

        $this->assertSame('In 5 days', RandevuTime::phrase('2026-09-28', $today));
        $this->assertSame('5 days ago', RandevuTime::phrase('2026-09-18', $today));
        $this->assertSame('In 2 months', RandevuTime::phrase('2026-11-23', $today));
        $this->assertSame('2 months ago', RandevuTime::phrase('2026-07-23', $today));
        $this->assertSame('In 1 year', RandevuTime::phrase('2027-09-23', $today));
        $this->assertSame('2 years ago', RandevuTime::phrase('2024-09-23', $today));
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
