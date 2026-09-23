<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\Follow;
use App\NativeComponents\Memories;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuDetails;
use App\NativeComponents\RandevuEdit;
use App\NativeComponents\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

class PeriodDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_offers_unit_checkboxes_defaulting_to_all_on(): void
    {
        Native::test(RandevuCreate::class)
            ->assertSet('show_years', true)
            ->assertSet('show_months', true)
            ->assertSet('show_days', true)
            ->assertSee('إظهار المدة بـ');
    }

    public function test_create_saves_chosen_units_subset(): void
    {
        $date = today()->addDays(40);

        Native::test(RandevuCreate::class)
            ->set('title', 'Trip')
            ->set('day', (string) $date->day)
            ->set('month', \App\Services\RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('show_months', false)
            ->call('save')
            ->assertReplacedWith('/');

        $randevu = Randevu::where('title', 'Trip')->firstOrFail();

        $this->assertTrue($randevu->show_years);
        $this->assertFalse($randevu->show_months);
        $this->assertTrue($randevu->show_days);
    }

    public function test_create_rejects_all_units_off_and_keeps_values(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(40);

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Trip')
            ->set('day', (string) $date->day)
            ->set('month', \App\Services\RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('show_years', false)
            ->set('show_months', false)
            ->set('show_days', false)
            ->call('save');

        $screen->assertNotSet('errors', []);
        $this->assertSame('Trip', $screen->get('title'));
        $this->assertDatabaseCount('randevus', 0);
    }

    public function test_edit_prefills_and_updates_units(): void
    {
        $randevu = Randevu::create([
            'title' => 'Trip',
            'occurs_on' => today()->addDays(40),
            'show_years' => true,
            'show_months' => false,
            'show_days' => true,
        ]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('show_years', true)
            ->assertSet('show_months', false)
            ->assertSet('show_days', true)
            ->set('show_years', false)
            ->set('show_days', false)
            ->set('show_months', true)
            ->call('update')
            ->assertReplacedWith('/');

        $fresh = $randevu->fresh();

        $this->assertFalse($fresh->show_years);
        $this->assertTrue($fresh->show_months);
        $this->assertFalse($fresh->show_days);
    }

    public function test_follow_card_shows_each_appointments_own_units(): void
    {
        Setting::set('locale', 'en');

        Randevu::create([
            'title' => 'Days only',
            'occurs_on' => today()->addDays(40),
            'show_years' => false,
            'show_months' => false,
            'show_days' => true,
        ]);
        Randevu::create([
            'title' => 'Years months',
            'occurs_on' => today()->addDays(400),
            'show_years' => true,
            'show_months' => true,
            'show_days' => false,
        ]);

        $subset = Randevu::where('title', 'Years months')->firstOrFail();

        Native::test(Follow::class)
            ->assertSee('In 40 days')
            ->assertSee($subset->relativePhrase());
    }

    public function test_memories_card_shows_each_memories_own_units(): void
    {
        Setting::set('locale', 'en');

        Randevu::create([
            'title' => 'Old days',
            'occurs_on' => today()->subDays(40),
            'show_years' => false,
            'show_months' => false,
            'show_days' => true,
        ]);

        Native::test(Memories::class)->assertSee('40 days ago');
    }

    public function test_hijri_entered_card_shows_both_dates_and_shared_difference(): void
    {
        Setting::set('locale', 'en');

        Native::test(RandevuCreate::class)
            ->set('title', 'Hijri night')
            ->call('useHijri')
            ->set('h_day', '10')
            ->set('h_month', 'ربيع الثاني')
            ->set('h_year', '1448')
            ->call('save')
            ->assertReplacedWith('/');

        $randevu = Randevu::where('title', 'Hijri night')->firstOrFail();

        // Both calendars stored from a Hijri-only entry.
        $this->assertSame('2026-09-23', $randevu->occurs_on->toDateString());
        $this->assertSame('hijri', $randevu->entered_in);

        Native::test(Follow::class)
            ->assertSee('Hijri night')
            ->assertSee('23 September 2026')
            ->assertSee($randevu->hijriLabel())
            ->assertSee($randevu->relativePhrase());
    }

    public function test_settings_has_no_period_section(): void
    {
        Native::test(Settings::class)
            ->assertSee('اللغة')
            ->assertDontSee('إظهار المدة بـ');

        Setting::set('locale', 'en');

        Native::test(Settings::class)->assertDontSee('Show distance as');
    }

    public function test_details_screen_shows_everything_and_links_edit(): void
    {
        Setting::set('locale', 'en');

        $randevu = Randevu::create(array_merge(
            ['title' => 'Full card', 'occurs_on' => today(), 'note' => 'Second floor'],
            Randevu::hijriTriple(today()->toDateString())
        ));

        $randevu = $randevu->fresh();

        Native::visit('/details/'.$randevu->id)
            ->assertSee('Full card')
            ->assertSee('Today')
            ->assertSee('23 September 2026')
            ->assertSee($randevu->hijriLabel())
            ->assertSee('Appointment')
            ->assertSee('Second floor');

        $screen = Native::test(RandevuDetails::class, ['id' => $randevu->id]);

        $this->assertSame('Full card', $screen->get('randevu')['title']);
    }

    public function test_details_screen_for_memory_shows_memory_kind(): void
    {
        Setting::set('locale', 'en');

        $randevu = Randevu::create(['title' => 'Old', 'occurs_on' => today()->subDay()]);

        Native::visit('/details/'.$randevu->id)->assertSee('Memory');
    }

    public function test_details_screen_for_missing_randevu_shows_fallback(): void
    {
        Setting::set('locale', 'en');

        Native::test(RandevuDetails::class, ['id' => 99999])
            ->assertSet('randevu', null)
            ->assertSee('This randevu no longer exists.');
    }

    public function test_details_route_resolves_via_visit(): void
    {
        $randevu = Randevu::create(['title' => 'Routed', 'occurs_on' => today()]);

        Native::visit('/details/'.$randevu->id)->assertSee('التفاصيل');
    }
}
