<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\Dashboard;
use App\NativeComponents\Settings;
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
            ->assertDontSee('ضيف أول ميعاد');
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
            ->assertSee('Entry calendar');
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
        $screen->assertElement('webview', fn ($n) => true);
        $screen
            ->assertSee('الإجمالي')
            ->assertSee('طريقة الإدخال')
            ->assertSee('ميلادي ولا هجري');
    }
}
