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
use App\Services\AppTheme;
use App\Services\RandevuHijri;
use App\Services\RandevuTime;
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

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Trip')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('show_months', false)
            ->call('save');

        $randevu = Randevu::where('title', 'Trip')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

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
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
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
            ->assertReplacedWith('/follow');

        $fresh = $randevu->fresh();

        $this->assertFalse($fresh->show_years);
        $this->assertTrue($fresh->show_months);
        $this->assertFalse($fresh->show_days);
    }

    public function test_create_saves_optional_time_and_hours_unit(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(2);

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Meeting')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('hour', '14')
            ->set('minute', '30')
            ->set('show_hours', true)
            ->call('save');

        $randevu = Randevu::where('title', 'Meeting')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        $this->assertSame('14:30', $randevu->occurs_time->format('H:i'));
        $this->assertTrue($randevu->show_hours);

        Native::test(Follow::class)->assertSee('In 2 days, 14 hours');
    }

    public function test_create_with_hour_only_means_top_of_hour(): void
    {
        $date = today()->addDays(2);

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Sharp')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('hour', '14')
            ->call('save');

        $randevu = Randevu::where('title', 'Sharp')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        $this->assertSame('14:00', $randevu->occurs_time->format('H:i'));
        $this->assertFalse($randevu->show_hours);
    }

    public function test_create_without_time_stores_null_time(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(40);

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Plain')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->call('save');

        $randevu = Randevu::where('title', 'Plain')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        $this->assertNull($randevu->occurs_time);
        $this->assertFalse($randevu->show_hours);

        // Time-less keeps the day-precision breakdown for the default units.
        $this->assertSame('In 1 month, 10 days', $randevu->relativePhrase());
    }

    public function test_create_allows_hours_as_the_only_unit(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(40);

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Countdown')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('hour', '14')
            ->set('minute', '30')
            ->set('show_years', false)
            ->set('show_months', false)
            ->set('show_days', false)
            ->set('show_hours', true)
            ->call('save');

        $randevu = Randevu::where('title', 'Countdown')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        $this->assertTrue($randevu->show_hours);
        $this->assertSame('In 974 hours', $randevu->relativePhrase());
    }

    public function test_edit_prefills_time_and_updates_it(): void
    {
        $randevu = Randevu::create([
            'title' => 'Trip',
            'occurs_on' => today()->addDays(40),
            'occurs_time' => '14:30',
            'show_hours' => true,
        ]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('hour', '14')
            ->assertSet('minute', '30')
            ->assertSet('show_hours', true)
            ->set('hour', '15')
            ->call('update')
            ->assertReplacedWith('/follow');

        $this->assertSame('15:30', $randevu->fresh()->occurs_time->format('H:i'));
    }

    public function test_edit_clearing_time_sets_null(): void
    {
        $randevu = Randevu::create([
            'title' => 'Trip',
            'occurs_on' => today()->addDays(40),
            'occurs_time' => '14:30',
            'show_hours' => true,
        ]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->set('hour', __('randevu.time_none'))
            ->set('minute', __('randevu.time_none'))
            ->set('show_hours', false)
            ->call('update')
            ->assertReplacedWith('/follow');

        $fresh = $randevu->fresh();

        $this->assertNull($fresh->occurs_time);
        $this->assertFalse($fresh->show_hours);
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

        // Enter a Hijri date that lands safely in the future so the card
        // appears on Follow regardless of the day the suite runs.
        $target = today()->addDays(30);
        [$hy, $hm, $hd] = RandevuHijri::fromGregorian($target->year, $target->month, $target->day);

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Hijri night')
            ->call('useHijri')
            ->set('h_day', (string) $hd)
            ->set('h_month', RandevuHijri::MONTH_NAMES[$hm - 1])
            ->set('h_year', (string) $hy)
            ->call('save');

        $randevu = Randevu::where('title', 'Hijri night')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        // Both calendars stored from a Hijri-only entry.
        $this->assertSame($target->toDateString(), $randevu->occurs_on->toDateString());
        $this->assertSame('hijri', $randevu->entered_in);

        Native::test(Follow::class)
            ->assertSee('Hijri night')
            ->assertSee($target->format('j F Y'))
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
            ->assertSee(today()->format('j F Y'))
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

    public function test_create_form_previews_live_phrase(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(40);

        $screen = Native::test(RandevuCreate::class)
            ->assertSee('The card will say')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year);

        // All units on: exact calendar breakdown.
        $screen->assertSee(RandevuTime::phraseFor($date->toDateString(), true, true, true, false));

        // Years off: months + days breakdown.
        $screen->set('show_years', false)
            ->assertSee(RandevuTime::phraseFor($date->toDateString(), false, true, true, false));

        // Days only: exact day count.
        $screen->set('show_months', false)
            ->assertSee(RandevuTime::phraseFor($date->toDateString(), false, false, true, false));

        // Months only: rounded single unit.
        $screen->set('show_days', false)->set('show_months', true)
            ->assertSee(RandevuTime::phraseFor($date->toDateString(), false, true, false, false));

        // All units off: falls back to the plain phrase.
        $screen->set('show_months', false)
            ->assertSee(RandevuTime::phraseFor($date->toDateString(), false, false, false, false));
    }

    public function test_create_form_previews_hours_with_time(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(2);

        Native::test(RandevuCreate::class)
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('hour', '14')
            ->set('minute', '30')
            ->set('show_years', false)
            ->set('show_months', false)
            ->set('show_days', false)
            ->set('show_hours', true)
            ->assertSee(RandevuTime::phraseFor($date->toDateString(), false, false, false, true, '14:30'));
    }

    public function test_create_form_preview_shows_placeholder_for_invalid_date(): void
    {
        Setting::set('locale', 'en');

        Native::test(RandevuCreate::class)
            ->set('month', 'April')
            ->set('day', '31')
            ->assertSee('Pick a valid date to see the distance');
    }

    public function test_create_form_previews_hijri_selection(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(40);
        [$hy, $hm, $hd] = RandevuHijri::fromGregorian($date->year, $date->month, $date->day);

        Native::test(RandevuCreate::class)
            ->press('useHijri')
            ->set('h_day', (string) $hd)
            ->set('h_month', RandevuHijri::MONTH_NAMES[$hm - 1])
            ->set('h_year', (string) $hy)
            ->assertSee(RandevuTime::phraseFor($date->toDateString(), true, true, true, false));
    }

    public function test_edit_form_previews_stored_phrase(): void
    {
        Setting::set('locale', 'en');

        $randevu = Randevu::create([
            'title' => 'Trip',
            'occurs_on' => today()->addDays(40),
            'show_years' => false,
            'show_months' => false,
            'show_days' => true,
        ]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSee('The card will say')
            ->assertSee(RandevuTime::phraseFor($randevu->occurs_on->toDateString(), false, false, true, false));
    }

    public function test_edit_form_previews_hijri_entered_phrase(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDays(40);
        $triple = Randevu::hijriTriple($date->toDateString());

        $randevu = Randevu::create([
            'title' => 'Hijri trip',
            'occurs_on' => $date->toDateString(),
            'hijri_year' => $triple['hijri_year'],
            'hijri_month' => $triple['hijri_month'],
            'hijri_day' => $triple['hijri_day'],
            'entered_in' => 'hijri',
            'show_years' => false,
            'show_months' => false,
            'show_days' => true,
        ]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSee(RandevuTime::phraseFor($date->toDateString(), false, false, true, false));
    }

    public function test_create_form_shows_preview_label_in_arabic(): void
    {
        Native::test(RandevuCreate::class)->assertSee('البطاقة هتقول');
    }

    public function test_create_form_preview_resolves_dark_palette(): void
    {
        Setting::set('theme', 'dark');
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class)
            ->set('month', 'April')
            ->set('day', '31');

        // Read AFTER mount via the sanctioned token reader: AppTheme
        // rewrites both config blocks on every apply (boot forces light
        // first), so the dark value only resolves once the mounted
        // screen forces dark.
        $muted = AppTheme::token('on-surface-variant');

        // Forced dark: the preview placeholder must carry the dark block's
        // muted token, not the light one.
        $screen->assertElement('text', fn ($n) => (($n['props']['text'] ?? null) === 'Pick a valid date to see the distance')
            && (($n['props']['color'] ?? null) === $muted));
    }

    public function test_follow_cards_show_urgency_tiers(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Now', 'occurs_on' => today()]);
        Randevu::create(['title' => 'Soon', 'occurs_on' => today()->addDays(3)]);
        Randevu::create(['title' => 'Later', 'occurs_on' => today()->addDays(40)]);

        $screen = Native::test(Follow::class);

        // Read AFTER mount via the sanctioned token reader: AppTheme
        // rewrites both config blocks on every apply, so raw config paths
        // can't be trusted here.
        $accentBg = AppTheme::token('accent');
        $accentFg = AppTheme::token('on-accent');
        $strongBg = AppTheme::token('primary');
        $strongFg = AppTheme::token('on-primary');
        $mutedBg = AppTheme::token('surface-variant');
        $mutedFg = AppTheme::token('on-surface');

        $screen
            ->assertElement('text', fn ($n) => (($n['props']['text'] ?? null) === 'Today')
                && (($n['style']['bg_color'] ?? null) === $accentBg)
                && (($n['props']['color'] ?? null) === $accentFg))
            ->assertElement('text', fn ($n) => (($n['props']['text'] ?? null) === 'In 3 days')
                && (($n['style']['bg_color'] ?? null) === $strongBg)
                && (($n['props']['color'] ?? null) === $strongFg))
            ->assertElement('text', fn ($n) => (($n['props']['text'] ?? null) === RandevuTime::phraseFor(today()->addDays(40)->toDateString(), true, true, true, false))
                && (($n['style']['bg_color'] ?? null) === $mutedBg)
                && (($n['props']['color'] ?? null) === $mutedFg));
    }

    public function test_follow_rings_use_urgency_fill(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Now', 'occurs_on' => today()]);
        Randevu::create(['title' => 'Soon', 'occurs_on' => today()->addDays(3)]);
        Randevu::create(['title' => 'Later', 'occurs_on' => today()->addDays(40)]);

        $screen = Native::test(Follow::class);

        $accent = AppTheme::token('accent');
        $primary = AppTheme::token('primary');
        $muted = AppTheme::token('on-surface-variant');

        $screen
            ->assertElement('webview', fn ($n) => str_contains((string) ($n['props']['html'] ?? ''), $accent)
                && str_contains((string) ($n['props']['html'] ?? ''), '>0</text>'))
            ->assertElement('webview', fn ($n) => str_contains((string) ($n['props']['html'] ?? ''), $primary)
                && str_contains((string) ($n['props']['html'] ?? ''), '>3</text>'))
            ->assertElement('webview', fn ($n) => str_contains((string) ($n['props']['html'] ?? ''), $muted)
                && str_contains((string) ($n['props']['html'] ?? ''), '>40</text>'));
    }

    public function test_memories_cards_keep_calendar_pills(): void
    {
        Setting::set('locale', 'en');
        $past = today()->subDays(40);

        Randevu::create(['title' => 'Old days', 'occurs_on' => $past->toDateString()]);
        Randevu::create(array_merge(
            ['title' => 'Old hijri', 'occurs_on' => $past->toDateString(), 'entered_in' => 'hijri'],
            Randevu::hijriTriple($past->toDateString())
        ));

        $screen = Native::test(Memories::class);

        // Calendar pills (no tiers here), with the AA-safe filled pairs.
        $primary = AppTheme::token('primary');
        $onPrimary = AppTheme::token('on-primary');
        $accent = AppTheme::token('accent');
        $onAccent = AppTheme::token('on-accent');

        $days = Randevu::where('title', 'Old days')->firstOrFail();
        $hijri = Randevu::where('title', 'Old hijri')->firstOrFail();

        $screen
            ->assertElement('text', fn ($n) => (($n['props']['text'] ?? null) === $days->relativePhrase())
                && (($n['style']['bg_color'] ?? null) === $primary)
                && (($n['props']['color'] ?? null) === $onPrimary))
            ->assertElement('text', fn ($n) => (($n['props']['text'] ?? null) === $hijri->relativePhrase())
                && (($n['style']['bg_color'] ?? null) === $accent)
                && (($n['props']['color'] ?? null) === $onAccent));
    }
}
