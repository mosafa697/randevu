<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\Dashboard;
use App\NativeComponents\Settings;
use App\Services\RandevuHijri;
use App\Services\RandevuTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_match_scopes_english(): void
    {
        Setting::set('locale', 'en');

        $today = Carbon::today();
        $dates = [
            $today->copy()->toDateString(), // today, upcoming
            $today->copy()->addDays(10)->toDateString(), // upcoming
            $today->copy()->addDays(20)->toDateString(), // upcoming, hijri-entered
            $today->copy()->subDays(5)->toDateString(), // memory
        ];

        Randevu::create(['title' => 'Today thing', 'occurs_on' => $dates[0], 'entered_in' => 'gregorian']);
        Randevu::create(['title' => 'Soon', 'occurs_on' => $dates[1], 'entered_in' => 'gregorian']);
        Randevu::create(array_merge(
            ['title' => 'Hijri occasion', 'occurs_on' => $dates[2], 'entered_in' => 'hijri'],
            Randevu::hijriTriple($dates[2])
        ));
        Randevu::create(['title' => 'Old', 'occurs_on' => $dates[3], 'entered_in' => 'gregorian']);

        $inMonth = fn (string $d): bool => $d >= $today->copy()->startOfMonth()->toDateString()
            && $d <= $today->copy()->endOfMonth()->toDateString();

        $screen = Native::test(Dashboard::class);

        $this->assertSame([
            'total' => 4,
            'upcoming' => 3,
            'memories' => 1,
            'today' => 1,
            'this_month' => count(array_filter($dates, $inMonth)),
        ], $screen->get('stats'));

        $this->assertTrue($screen->get('hasData'));
        $this->assertSame(3, $screen->get('gregorianCount'));
        $this->assertSame(1, $screen->get('hijriCount'));

        $screen
            ->assertSee('Total')
            ->assertSee('Entry calendar')
            ->assertSee('Gregorian vs Hijri-entered');
    }

    public function test_dashboard_empty_state_arabic(): void
    {
        Native::test(Dashboard::class)
            ->assertSee('ضيف ميعاد عشان تشوف الإحصائيات هنا')
            ->assertMissingElement('button', fn ($node) => ($node['props']['label'] ?? null) === __('randevu.empty_cta'));
    }

    public function test_dashboard_shows_current_dates_in_each_locale(): void
    {
        $this->travelTo(Carbon::parse('2026-11-02 12:34:56'), function (): void {
            foreach (['ar', 'ar_fusha', 'en'] as $locale) {
                Setting::set('locale', $locale);
                $screen = Native::test(Dashboard::class);
                [$hijriYear, $hijriMonth, $hijriDay] = RandevuHijri::fromGregorian(
                    (int) today()->year,
                    (int) today()->month,
                    (int) today()->day,
                );
                $hijriDate = RandevuHijri::format($hijriYear, $hijriMonth, $hijriDay)
                    .' '.RandevuHijri::hijriSuffix();

                $screen
                    ->assertSet('todayGregorianDate', RandevuTime::absolute(today()))
                    ->assertSet('todayHijriDate', $hijriDate)
                    ->assertSet('deviceTimezone', 'UTC')
                    ->assertSee(__('randevu.dash_date_section'))
                    ->assertSee(__('randevu.gregorian_label'))
                    ->assertSee(__('randevu.hijri_label'));
            }
        });
    }

    public function test_dashboard_dates_and_events_use_the_configured_timezone(): void
    {
        Setting::set('locale', 'en');
        config(['app.timezone' => 'America/Los_Angeles']);

        Randevu::create(['title' => 'Local today', 'occurs_on' => '2026-11-02']);
        Randevu::create(['title' => 'UTC today', 'occurs_on' => '2026-11-03']);
        Randevu::create(['title' => 'Local yesterday', 'occurs_on' => '2026-11-01']);

        $this->travelTo(Carbon::parse('2026-11-03 01:30:00', 'UTC'), function (): void {
            $screen = Native::test(Dashboard::class);
            $localToday = Carbon::now('America/Los_Angeles')->startOfDay();
            [$hijriYear, $hijriMonth, $hijriDay] = RandevuHijri::fromGregorian(
                (int) $localToday->year,
                (int) $localToday->month,
                (int) $localToday->day,
            );

            $screen
                ->assertSet('deviceTimezone', 'America/Los_Angeles')
                ->assertSet('todayGregorianDate', RandevuTime::absolute($localToday))
                ->assertSet(
                    'todayHijriDate',
                    RandevuHijri::format($hijriYear, $hijriMonth, $hijriDay).' '.RandevuHijri::hijriSuffix(),
                )
                ->assertSet('todayEvents', [
                    ['id' => Randevu::where('title', 'Local today')->value('id'), 'title' => 'Local today'],
                ]);

            $this->assertSame(1, $screen->get('stats')['today']);
            $this->assertSame(2, $screen->get('stats')['upcoming']);
            $this->assertSame(1, $screen->get('stats')['memories']);
        });
    }

    public function test_dashboard_lists_today_events_as_links_to_their_details(): void
    {
        Setting::set('locale', 'en');
        $first = Randevu::create(['title' => 'Morning appointment', 'occurs_on' => today()]);
        $second = Randevu::create(['title' => 'Evening appointment', 'occurs_on' => today()]);
        Randevu::create(['title' => 'Tomorrow appointment', 'occurs_on' => today()->addDay()]);
        Randevu::create(['title' => 'Yesterday memory', 'occurs_on' => today()->subDay()]);

        $screen = Native::test(Dashboard::class)
            ->assertSet('todayEvents', [
                ['id' => $first->id, 'title' => 'Morning appointment'],
                ['id' => $second->id, 'title' => 'Evening appointment'],
            ])
            ->assertSee(__('randevu.dash_today_events'))
            ->assertSee('Morning appointment')
            ->assertSee('Evening appointment');

        $screen
            ->press('today-event-'.$first->id)
            ->followNavigation()
            ->assertSee('Morning appointment');
    }

    public function test_dashboard_poll_refreshes_dates_and_today_events(): void
    {
        Setting::set('locale', 'en');
        $tomorrow = today()->addDay();

        Randevu::create(['title' => 'Becomes today', 'occurs_on' => $tomorrow]);

        $screen = Native::test(Dashboard::class)
            ->assertSet('todayEvents', []);

        $this->travelTo($tomorrow->copy()->setTime(12, 0), function () use ($screen): void {
            $screen
                ->firePoll('refresh')
                ->assertSet('todayEvents', [
                    ['id' => Randevu::where('title', 'Becomes today')->value('id'), 'title' => 'Becomes today'],
                ])
                ->assertSet('todayGregorianDate', RandevuTime::absolute(today()));
        });
    }

    public function test_dashboard_route_reachable_without_breaking_follow(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()]);

        Native::visit('/')->assertSee('Total');
        Native::visit('/follow')->assertSee('Dentist');
    }

    public function test_dashboard_with_memories_only_shows_stats_and_split(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Old', 'occurs_on' => today()->subDays(10), 'entered_in' => 'gregorian']);

        $screen = Native::test(Dashboard::class);

        $this->assertTrue($screen->get('hasData'));
        $this->assertSame(0, $screen->get('stats')['upcoming']);
        $this->assertSame(1, $screen->get('stats')['memories']);

        $screen
            ->assertSee('Total')
            ->assertSee('Entry calendar')
            ->assertSet('todayEvents', [])
            ->assertMissingElement('pressable', fn ($node) => str_starts_with((string) ($node['ref'] ?? ''), 'today-event-'));
    }

    public function test_dashboard_renders_arabic_data_in_dark_mode(): void
    {
        Setting::set('locale', 'ar');
        Native::test(Settings::class)->press('useDark');

        Randevu::create(['title' => 'Dentist', 'occurs_on' => today(), 'entered_in' => 'gregorian']);
        Randevu::create(array_merge(
            ['title' => 'Old', 'occurs_on' => today()->subDays(3), 'entered_in' => 'hijri'],
            Randevu::hijriTriple(today()->subDays(3)->toDateString())
        ));

        $screen = Native::test(Dashboard::class);

        // Dark palette applied and the chart rendered with Arabic copy.
        $this->assertSame('dark', $screen->get('theme'));
        $screen
            ->assertSee(__('randevu.dash_date_section'))
            ->assertSee('الإجمالي')
            ->assertSee('طريقة الإدخال')
            ->assertSee('ميلادي ولا هجري');
    }
}
