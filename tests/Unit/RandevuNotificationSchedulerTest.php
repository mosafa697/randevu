<?php

namespace Tests\Unit;

use App\Models\Randevu;
use App\Models\Setting;
use App\Services\RandevuHijri;
use App\Services\RandevuNotificationBridge;
use App\Services\RandevuNotificationScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class RandevuNotificationSchedulerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_appointment_reminders_use_local_time_and_skip_expired_or_late_rules(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 08:30:00'));
        Setting::set('notifications_enabled', '1');

        Randevu::create([
            'title' => 'Tomorrow meeting',
            'occurs_on' => '2026-09-25',
            'occurs_time' => '10:00',
            'reminder_rule' => '1d',
        ]);
        Randevu::create([
            'title' => 'Time has passed',
            'occurs_on' => '2026-09-23',
            'occurs_time' => '08:00',
            'reminder_rule' => 'at_time',
        ]);
        Randevu::create([
            'title' => 'Before opening',
            'occurs_on' => '2026-09-24',
            'occurs_time' => '08:00',
            'reminder_rule' => 'morning_of',
        ]);

        $schedules = app(RandevuNotificationScheduler::class)->buildSchedules();

        $this->assertCount(1, $schedules);
        $this->assertSame('appointment-1', $schedules[0]['id']);
        $this->assertSame(Carbon::parse('2026-09-24 09:00:00')->getTimestamp() * 1000, $schedules[0]['fireAtMillis']);
        $this->assertSame(Carbon::parse('2026-09-25 10:00:00')->getTimestamp() * 1000, $schedules[0]['eventAtMillis']);
    }

    public function test_timeless_appointment_defaults_to_nine_am_and_notifications_can_be_disabled_globally(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-23 08:00:00'));
        Randevu::create([
            'title' => 'Timeless',
            'occurs_on' => '2026-09-24',
            'reminder_rule' => 'at_time',
        ]);

        $schedules = app(RandevuNotificationScheduler::class)->buildSchedules();

        $this->assertCount(1, $schedules);
        $this->assertSame(Carbon::parse('2026-09-24 09:00:00')->getTimestamp() * 1000, $schedules[0]['fireAtMillis']);
        $this->assertSame('09:00', $schedules[0]['eventTime']);

        Setting::set('notifications_enabled', '0');

        $this->assertSame([], app(RandevuNotificationScheduler::class)->buildSchedules());
    }

    public function test_sync_skips_when_notification_bridge_is_not_registered(): void
    {
        $bridge = $this->mock(RandevuNotificationBridge::class);
        $bridge->shouldReceive('available')->once()->andReturn(true);
        $bridge->shouldReceive('status')->once()->andReturn([
            'available' => false,
            'notifications_granted' => false,
            'exact_alarms_granted' => false,
            'timezone' => null,
        ]);
        $bridge->shouldNotReceive('sync');

        (new RandevuNotificationScheduler($bridge))->sync();
    }

    public function test_gregorian_monthly_and_yearly_anniversaries_clamp_leap_day(): void
    {
        App::setLocale('en');
        Carbon::setTestNow(Carbon::parse('2026-02-28 10:00:00'));
        Randevu::create([
            'title' => 'Leap day memory',
            'occurs_on' => '2024-02-29',
            'entered_in' => 'gregorian',
            'anniversary_mode' => 'both',
            'anniversary_lead_days' => 0,
        ]);

        $schedules = app(RandevuNotificationScheduler::class)->buildSchedules();

        $this->assertCount(2, $schedules);
        $this->assertSame('anniversary-1-yearly', $schedules[0]['id']);
        $this->assertSame(
            Carbon::parse('2027-02-28 09:00:00')->getTimestamp() * 1000,
            $schedules[0]['fireAtMillis']
        );
        $this->assertStringStartsWith('from 3 years', $schedules[0]['body']);
        $this->assertSame('anniversary-1-monthly', $schedules[1]['id']);
        $this->assertSame(
            Carbon::parse('2026-03-29 09:00:00')->getTimestamp() * 1000,
            $schedules[1]['fireAtMillis']
        );
        $this->assertStringStartsWith('from 25 months', $schedules[1]['body']);
    }

    public function test_future_appointment_anniversary_starts_after_the_original_event(): void
    {
        App::setLocale('en');
        Carbon::setTestNow(Carbon::parse('2026-09-23 08:00:00'));
        Randevu::create([
            'title' => 'Future anniversary',
            'occurs_on' => '2027-09-25',
            'occurs_time' => '10:00',
            'reminder_rule' => '1d',
            'anniversary_mode' => 'yearly',
        ]);

        $schedules = app(RandevuNotificationScheduler::class)->buildSchedules();

        $this->assertCount(2, $schedules);
        $this->assertSame('appointment-1', $schedules[0]['id']);
        $this->assertSame('anniversary-1-yearly', $schedules[1]['id']);
        $this->assertSame(
            Carbon::parse('2028-09-25 10:00:00')->getTimestamp() * 1000,
            $schedules[1]['fireAtMillis']
        );
        $this->assertStringStartsWith('from 1 year', $schedules[1]['body']);
    }

    public function test_hijri_yearly_anniversary_clamps_a_thirty_day_month(): void
    {
        App::setLocale('en');
        [$baseYear, $baseMonth, $baseDay] = [1447, 12, 30];
        [$baseGregorianYear, $baseGregorianMonth, $baseGregorianDay] = RandevuHijri::toGregorian(
            $baseYear,
            $baseMonth,
            $baseDay
        );
        [$targetYear, $targetMonth, $targetDay] = RandevuHijri::toGregorian(1448, 12, 29);
        $targetDate = Carbon::create($targetYear, $targetMonth, $targetDay);
        Carbon::setTestNow($targetDate->copy()->subDays(2)->setTime(8, 0));
        Randevu::create([
            'title' => 'Hijri yearly memory',
            'occurs_on' => sprintf('%04d-%02d-%02d', $baseGregorianYear, $baseGregorianMonth, $baseGregorianDay),
            'entered_in' => 'hijri',
            'hijri_year' => $baseYear,
            'hijri_month' => $baseMonth,
            'hijri_day' => $baseDay,
            'anniversary_mode' => 'yearly',
        ]);

        $schedules = app(RandevuNotificationScheduler::class)->buildSchedules();

        $this->assertCount(1, $schedules);
        $this->assertSame(
            $targetDate->copy()->setTime(9, 0)->getTimestamp() * 1000,
            $schedules[0]['fireAtMillis']
        );
        $this->assertSame('hijri', $schedules[0]['calendar']);
        $this->assertSame(30, $schedules[0]['baseDay']);
    }

    public function test_hijri_monthly_anniversary_clamps_to_shorter_month(): void
    {
        App::setLocale('en');
        [$baseYear, $baseMonth, $baseDay] = [1447, 1, 30];
        [$baseGregorianYear, $baseGregorianMonth, $baseGregorianDay] = RandevuHijri::toGregorian(
            $baseYear,
            $baseMonth,
            $baseDay
        );
        [$targetYear, $targetMonth, $targetDay] = RandevuHijri::toGregorian(1447, 2, 29);
        $targetDate = Carbon::create($targetYear, $targetMonth, $targetDay);
        Carbon::setTestNow($targetDate->copy()->subDays(2)->setTime(8, 0));
        Randevu::create([
            'title' => 'Hijri monthly memory',
            'occurs_on' => sprintf('%04d-%02d-%02d', $baseGregorianYear, $baseGregorianMonth, $baseGregorianDay),
            'entered_in' => 'hijri',
            'hijri_year' => $baseYear,
            'hijri_month' => $baseMonth,
            'hijri_day' => $baseDay,
            'anniversary_mode' => 'monthly',
        ]);

        $schedules = app(RandevuNotificationScheduler::class)->buildSchedules();

        $this->assertCount(1, $schedules);
        $this->assertSame(
            $targetDate->copy()->setTime(9, 0)->getTimestamp() * 1000,
            $schedules[0]['fireAtMillis']
        );
        $this->assertStringStartsWith('from 1 month', $schedules[0]['body']);
    }
}
