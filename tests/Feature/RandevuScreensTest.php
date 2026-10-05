<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\Dashboard;
use App\NativeComponents\Follow;
use App\NativeComponents\Memories;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuDetails;
use App\NativeComponents\RandevuEdit;
use App\NativeComponents\Settings;
use App\Services\CoverImage;
use App\Services\RandevuHijri;
use App\Services\RandevuTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

class RandevuScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_empty_state_shows_only_add_button(): void
    {
        Native::test(Follow::class)
            ->assertSee('ضيف أول ميعاد')
            ->assertSee('لسه مفيش مواعيد')
            ->assertSee('ضيف أول ميعاد عشان تتابعه هنا')
            ->assertSee('المواعيد اللي تاريخها النهاردة أو بعد كده بتظهر هنا.')
            ->assertSee('تلميح: تقدر تدخل التاريخ بالهجري من شاشة ميعاد جديد.')
            ->assertDontSee('مفيش مواعيد لسه')
            ->assertDontSee('عشان تتابعها');

        Setting::set('locale', 'en');

        Native::test(Follow::class)
            ->assertSee('Appointments dated today or later show up here.')
            ->assertSee('Tip: you can enter Hijri dates from the New screen.');
    }

    public function test_follow_lists_appointments_only_english(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Dentist', 'occurs_on' => today(), 'note' => 'Bring card']);
        Randevu::create(['title' => 'Trip', 'occurs_on' => today()->addDays(3)]);
        Randevu::create(['title' => 'Graduation', 'occurs_on' => today()->subDays(2)]);

        Native::test(Follow::class)
            ->assertSee('Today')
            ->assertSee('Dentist')
            ->assertSee('Trip')
            ->assertSee('In 3 days')
            ->assertSee('Bring card')
            ->assertDontSee('Graduation')
            ->assertDontSee('Coming up')
            ->assertDontSee('Memories');
    }

    public function test_follow_lists_appointments_only_arabic(): void
    {
        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()]);
        Randevu::create(['title' => 'Trip', 'occurs_on' => today()->addDays(3)]);
        Randevu::create(['title' => 'Graduation', 'occurs_on' => today()->subDays(2)]);

        Native::test(Follow::class)
            ->assertSee('النهاردة')
            ->assertSee('بعد 3 أيام')
            ->assertDontSee('Graduation')
            ->assertDontSee('اللي جاي');
    }

    public function test_memories_screen_lists_memories_newest_first(): void
    {
        Randevu::create(['title' => 'Old', 'occurs_on' => today()->subDays(10)]);
        Randevu::create(['title' => 'Recent', 'occurs_on' => today()->subDays(2)]);
        Randevu::create(['title' => 'Future', 'occurs_on' => today()->addDays(2)]);

        Native::visit('/memories')
            ->assertSee('Recent')
            ->assertSee('Old')
            ->assertDontSee('Future');
    }

    public function test_memories_empty_state_shows_only_add_button(): void
    {
        Native::visit('/memories')
            ->assertSee('ضيف أول ميعاد')
            ->assertSee('لسه مفيش ذكريات محفوظة')
            ->assertSee('ضيف ميعاد عشان يظهر هنا بعد ما يعدى')
            ->assertSee('المواعيد اللي عدت بتتحفظ هنا كذكريات.')
            ->assertDontSee('مفيش مواعيد لسه');

        Setting::set('locale', 'en');

        Native::visit('/memories')
            ->assertSee('Past appointments are kept here as memories.');
    }

    public function test_create_rejects_invalid_input_and_keeps_values(): void
    {
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class)
            ->set('title', '')
            ->set('day', '30')
            ->set('month', 'February')
            ->set('year', (string) today()->year)
            ->call('save');

        $screen->assertNotSet('errors', []);
        $this->assertSame('', $screen->get('title'));
        // Feb 30 does not survive: picking February clamps the day to 28.
        $this->assertSame('28', $screen->get('day'));
        $this->assertDatabaseCount('randevus', 0);
    }

    public function test_create_clamps_day_to_month_length_and_saves(): void
    {
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Feb meeting')
            ->set('day', '30')
            ->set('month', 'February')
            ->set('year', '2026')
            ->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'Feb meeting')->firstOrFail()->id);

        $this->assertSame(
            '2026-02-28',
            Randevu::where('title', 'Feb meeting')->firstOrFail()->occurs_on->toDateString()
        );

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'April deadline')
            ->set('day', '31')
            ->set('month', 'April')
            ->set('year', '2026')
            ->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'April deadline')->firstOrFail()->id);

        $this->assertSame(
            '2026-04-30',
            Randevu::where('title', 'April deadline')->firstOrFail()->occurs_on->toDateString()
        );
    }

    public function test_create_keeps_valid_leap_day_and_saves(): void
    {
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Leap day')
            ->set('year', '2024')
            ->set('month', 'February')
            ->set('day', '29')
            ->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'Leap day')->firstOrFail()->id);

        $this->assertSame(
            '2024-02-29',
            Randevu::where('title', 'Leap day')->firstOrFail()->occurs_on->toDateString()
        );
    }

    public function test_create_has_calendar_mode_buttons_and_chips(): void
    {
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class);

        $screen->assertElement('button', fn ($n) => ($n['props']['label'] ?? '') === 'Gregorian');
        $screen->assertElement('button', fn ($n) => ($n['props']['label'] ?? '') === 'Hijri');
        $screen->assertElement('chip', fn ($n) => ($n['props']['label'] ?? '') === 'Years');
        $screen->assertElement('chip', fn ($n) => ($n['props']['label'] ?? '') === 'Months');
        $screen->assertElement('chip', fn ($n) => ($n['props']['label'] ?? '') === 'Days');
        $screen->assertElement('chip', fn ($n) => ($n['props']['label'] ?? '') === 'Hours');
        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Hour');
        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Minute');
    }

    public function test_create_calendar_buttons_switch_mode(): void
    {
        $screen = Native::test(RandevuCreate::class);

        $this->assertSame('gregorian', $screen->get('calendar_mode'));
        $this->assertSame(0, $screen->get('calendarIndex'));

        $screen->press('useHijri');

        $this->assertSame('hijri', $screen->get('calendar_mode'));
        $this->assertSame(1, $screen->get('calendarIndex'));

        $screen->press('useGregorian');

        $this->assertSame('gregorian', $screen->get('calendar_mode'));
        $this->assertSame(0, $screen->get('calendarIndex'));
    }

    public function test_date_selects_weight_month_wider_than_day(): void
    {
        Setting::set('locale', 'en');

        // Day rides at w-24 like Year since 601d1b5 ("Increase width of day
        // select inputs"); Month stays the flex-1 wide one.
        $narrow = fn ($n) => ($n['layout']['width'] ?? null) == 96
            && ($n['layout']['flex_shrink'] ?? null) == 0;
        $wide = fn ($n) => ($n['layout']['flex_grow'] ?? null) == 1
            && ! isset($n['layout']['width']);

        $screen = Native::test(RandevuCreate::class);

        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Day' && $narrow($n));
        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Month' && $wide($n));
        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Year' && $narrow($n));

        $screen->press('useHijri');

        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Day' && $narrow($n));
        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Month' && $wide($n));
        $screen->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Year' && $narrow($n));

        $randevu = Randevu::create(['title' => 'Weighted', 'occurs_on' => today()]);

        $edit = Native::test(RandevuEdit::class, ['id' => $randevu->id]);

        $edit->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Day' && $narrow($n));
        $edit->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Month' && $wide($n));
        $edit->assertElement('select', fn ($n) => ($n['props']['label'] ?? '') === 'Year' && $narrow($n));
    }

    public function test_create_chips_toggle_period_units(): void
    {
        $screen = Native::test(RandevuCreate::class);

        $this->assertTrue($screen->get('show_years'));
        $screen->call('toggleYears');
        $this->assertFalse($screen->get('show_years'));
        $screen->call('toggleYears');
        $this->assertTrue($screen->get('show_years'));
    }

    public function test_create_saves_and_shows_new_details(): void
    {
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Dentist')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('note', 'Second floor')
            ->call('save');

        $randevu = Randevu::where('title', 'Dentist')->firstOrFail();
        $screen->assertReplacedWith('/details/'.$randevu->id);

        // The landing screen actually renders the new appointment.
        Native::visit('/details/'.$randevu->id)
            ->assertSee('Dentist')
            ->assertSee('Second floor');

        $this->assertDatabaseHas('randevus', ['title' => 'Dentist', 'note' => 'Second floor']);
        $this->assertSame(
            $date->toDateString(),
            Randevu::where('title', 'Dentist')->firstOrFail()->occurs_on->toDateString()
        );
    }

    public function test_create_saves_chosen_color_normalized(): void
    {
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Colorful')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('color', '2563eb')
            ->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'Colorful')->firstOrFail()->id);

        $this->assertSame('#2563EB', Randevu::where('title', 'Colorful')->firstOrFail()->color);
    }

    public function test_create_rejects_invalid_color_hex(): void
    {
        Setting::set('locale', 'en');
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Bad color')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('color', '#nope')
            ->call('save');

        $screen->assertNotSet('errors', []);
        $screen->assertSee('The color format is invalid.');
        $this->assertDatabaseCount('randevus', 0);
    }

    public function test_edit_prefills_and_saves(): void
    {
        $randevu = Randevu::create(['title' => 'Old', 'occurs_on' => today(), 'note' => 'x']);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('title', 'Old')
            ->assertSet('month', RandevuTime::monthNames()[today()->month - 1])
            ->set('title', 'New')
            ->call('update')
            ->assertReplacedWith('/follow');

        $this->assertSame('New', $randevu->fresh()->title);
    }

    public function test_create_prefills_today_with_values_present_in_options(): void
    {
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class);
        $today = today();

        $this->assertSame((string) $today->day, $screen->get('day'));
        $this->assertSame(RandevuTime::monthNames()[$today->month - 1], $screen->get('month'));
        $this->assertSame((string) $today->year, $screen->get('year'));

        // The prefilled value must be one of the dropdown options, or the
        // native trigger cannot display/select it.
        $this->assertContains($screen->get('day'), $screen->get('dayOptions'));
        $this->assertContains($screen->get('month'), $screen->get('monthOptions'));
        $this->assertContains($screen->get('year'), $screen->get('yearOptions'));
        $this->assertContains($screen->get('h_day'), $screen->get('hDayOptions'));
        $this->assertContains($screen->get('h_month'), $screen->get('hMonthOptions'));
        $this->assertContains($screen->get('h_year'), $screen->get('hYearOptions'));

        // Year lists are newest-first so the opened dropdown starts next to
        // the current year instead of a century back.
        $yearOptions = $screen->get('yearOptions');
        $this->assertSame((string) ($today->year + 30), $yearOptions[0]);
        $this->assertSame(30, array_search((string) $today->year, $yearOptions, true));
    }

    public function test_edit_prefill_matches_year_options(): void
    {
        Setting::set('locale', 'en');

        $randevu = Randevu::create(['title' => 'Prefilled', 'occurs_on' => today()]);

        $screen = Native::test(RandevuEdit::class, ['id' => $randevu->id]);

        $this->assertSame((string) today()->year, $screen->get('year'));
        $this->assertContains($screen->get('year'), $screen->get('yearOptions'));
        $this->assertContains($screen->get('h_year'), $screen->get('hYearOptions'));
    }

    public function test_edit_prefills_color_and_persists_changes(): void
    {
        $randevu = Randevu::create(['title' => 'Keep', 'occurs_on' => today(), 'color' => '#DB2777']);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('color', '#DB2777')
            ->call('pickPurple')
            ->call('update')
            ->assertReplacedWith('/follow');

        $this->assertSame('#7C3AED', $randevu->fresh()->color);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->call('clearColor')
            ->call('update');

        $this->assertNull($randevu->fresh()->color);
    }

    public function test_sliders_mix_custom_hex_on_create(): void
    {
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Mixed')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('color_r', 255)
            ->set('color_g', 99)
            ->set('color_b', 71)
            ->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'Mixed')->firstOrFail()->id);

        $this->assertSame('#FF6347', Randevu::where('title', 'Mixed')->firstOrFail()->color);
    }

    public function test_preset_tap_moves_sliders_to_match(): void
    {
        Setting::set('locale', 'en');

        Native::test(RandevuCreate::class)
            ->call('pickBlue')
            ->assertSet('color', '#2563EB')
            ->assertSet('color_r', 37)
            ->assertSet('color_g', 99)
            ->assertSet('color_b', 235)
            ->assertSee('#2563EB');
    }

    public function test_clear_color_resets_sliders_and_saves_null(): void
    {
        $date = today()->addDay();

        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Cleared')
            ->set('day', (string) $date->day)
            ->set('month', RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('color_r', 255)
            ->set('color_g', 99)
            ->set('color_b', 71)
            ->call('clearColor');

        $screen
            ->assertSet('color', '')
            ->assertSet('color_r', 0)
            ->assertSet('color_g', 0)
            ->assertSet('color_b', 0);

        $screen->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'Cleared')->firstOrFail()->id);

        $this->assertNull(Randevu::where('title', 'Cleared')->firstOrFail()->color);
    }

    public function test_edit_prefills_sliders_from_custom_color(): void
    {
        $randevu = Randevu::create(['title' => 'Mixed', 'occurs_on' => today(), 'color' => '#FF6347']);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('color', '#FF6347')
            ->assertSet('color_r', 255)
            ->assertSet('color_g', 99)
            ->assertSet('color_b', 71);
    }

    public function test_follow_card_data_and_dot_include_the_color(): void
    {
        Randevu::create(['title' => 'Colorful', 'occurs_on' => today()->addDay(), 'color' => '#DB2777']);
        Randevu::create(['title' => 'Plain', 'occurs_on' => today()->addDays(2)]);

        $screen = Native::test(Follow::class);

        $this->assertSame(
            '#DB2777',
            collect($screen->get('appointments'))->firstWhere('title', 'Colorful')['color']
        );
    }

    public function test_follow_card_accent_only_for_colored_randevu(): void
    {
        Randevu::create(['title' => 'Colorful', 'occurs_on' => today()->addDay(), 'color' => '#DB2777']);
        Randevu::create(['title' => 'Plain', 'occurs_on' => today()->addDays(2)]);

        $tree = Native::test(Follow::class)->tree();

        $accents = $this->collectBgColors($tree);

        $this->assertContains('#DB2777', $accents);
        $this->assertCount(1, array_keys($accents, '#DB2777', true));
    }

    public function test_details_presents_and_renders_color_accent(): void
    {
        $randevu = Randevu::create(['title' => 'Colorful', 'occurs_on' => today()->addDay(), 'color' => '#DB2777']);

        $screen = Native::test(RandevuDetails::class, ['id' => $randevu->id]);

        $this->assertSame('#DB2777', $screen->get('randevu')['color']);

        $accents = $this->collectBgColors($screen->tree());

        $this->assertContains('#DB2777', $accents);
    }

    /** @return list<string> */
    private function collectBgColors(array $node): array
    {
        $colors = [];

        $walk = function ($current) use (&$walk, &$colors): void {
            if (! is_array($current)) {
                return;
            }

            if (isset($current['style']['bg_color']) && is_string($current['style']['bg_color'])) {
                $colors[] = $current['style']['bg_color'];
            }

            foreach ($current['children'] ?? [] as $child) {
                $walk($child);
            }
        };

        $walk($node);

        return $colors;
    }

    public function test_follow_card_has_ring_webview_and_countdown_pill(): void
    {
        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()->addDays(5)]);

        $screen = Native::test(Follow::class);

        $screen->assertElement('webview', fn ($n) => isset($n['props']['html']) && str_contains($n['props']['html'], '<svg'));
        $screen->assertSee('بعد 5 أيام');
    }

    public function test_memories_card_has_ring_webview(): void
    {
        Randevu::create(['title' => 'Old', 'occurs_on' => today()->subDays(10)]);

        Native::visit('/memories')
            ->assertElement('webview', fn ($n) => isset($n['props']['html']) && str_contains($n['props']['html'], '<svg'));
    }

    public function test_titles_and_section_headers_render_in_amiri_bold(): void
    {
        Setting::set('locale', 'en');
        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()->addDay()]);

        $heading = fn ($n) => ($n['props']['font_name'] ?? null) === 'heading';

        Native::test(Follow::class)
            ->assertElement('text', fn ($n) => $heading($n) && ($n['props']['text'] ?? '') === 'Dentist');

        Native::test(RandevuCreate::class)
            ->assertElement('text', fn ($n) => $heading($n) && ($n['props']['text'] ?? '') === 'Show distance as');

        Native::test(Settings::class)
            ->assertElement('text', fn ($n) => $heading($n) && ($n['props']['text'] ?? '') === 'Language');

        Native::visit('/')
            ->assertElement('native_root_tabs', fn ($n) => ($n['props']['nav_font_name'] ?? null) === 'heading');
    }

    public function test_every_route_renders_native_tab_chrome(): void
    {
        Setting::set('locale', 'en');
        $randevu = Randevu::create(['title' => 'Dentist', 'occurs_on' => today()->addDay()]);

        $routes = [
            '/' => 'Dashboard',
            '/follow' => 'Follow',
            '/create' => 'New',
            '/memories' => 'Memories',
            '/settings' => 'Settings',
            '/details/'.$randevu->id => null,
            '/edit/'.$randevu->id => null,
        ];

        foreach ($routes as $uri => $activeTab) {
            $screen = Native::visit($uri)->assertHasTabBar();

            if ($activeTab !== null) {
                $screen->assertTabActive($activeTab);
            }
        }
    }

    public function test_tab_bar_uses_theme_tokens_and_the_chrome_font(): void
    {
        Setting::set('locale', 'en');

        $tree = Native::visit('/')->tree();

        $this->assertSame('#6F63DB', $tree['props']['active_color'] ?? null);
        $this->assertSame('#69647D', $tree['props']['text_color'] ?? null);
        $this->assertSame('label', $tree['props']['font_name'] ?? null);
        $this->assertSame('labeled', $tree['props']['label_visibility'] ?? null);
        $this->assertArrayNotHasKey('background_color', $tree['props']);

        $labels = array_values(array_map(
            fn ($node) => $node['props']['label'] ?? null,
            array_filter($tree['children'], fn ($node) => ($node['type'] ?? null) === 'bottom_nav_item'),
        ));

        $this->assertSame(['Dashboard', 'Follow', 'New', 'Memories', 'Settings'], $labels);
    }

    public function test_body_copy_is_not_heading_font(): void
    {
        Setting::set('locale', 'en');
        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()->addDay(), 'note' => 'Bring card']);

        Native::test(Follow::class)
            ->assertMissingElement('text', fn ($n) => ($n['props']['font_name'] ?? null) === 'heading'
                && ($n['props']['text'] ?? '') === 'Bring card');
    }

    public function test_edit_rejects_invalid_input(): void
    {
        $randevu = Randevu::create(['title' => 'Keep me', 'occurs_on' => today()]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->set('title', '')
            ->call('update')
            ->assertNotSet('errors', []);

        $this->assertSame('Keep me', $randevu->fresh()->title);
    }

    public function test_delete_asks_confirmation_then_removes(): void
    {
        $randevu = Randevu::create(['title' => 'Gone', 'occurs_on' => today()]);

        $screen = Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->call('askDelete')
            ->assertSet('confirmingDelete', true)
            ->assertSee('تمسح الميعاد ده؟');

        $screen->call('destroy')->assertReplacedWith('/follow');

        $this->assertDatabaseMissing('randevus', ['id' => $randevu->id]);
    }

    public function test_create_in_hijri_mode_stores_both_calendars(): void
    {
        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Ramadan night')
            ->call('useHijri')
            ->set('h_day', '10')
            ->set('h_month', 'ربيع الثاني')
            ->set('h_year', '1448')
            ->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'Ramadan night')->firstOrFail()->id);

        $randevu = Randevu::where('title', 'Ramadan night')->firstOrFail();

        $this->assertSame('2026-09-23', $randevu->occurs_on->toDateString());
        $this->assertSame([1448, 4, 10], [$randevu->hijri_year, $randevu->hijri_month, $randevu->hijri_day]);
        $this->assertSame('hijri', $randevu->entered_in);
    }

    public function test_create_clamps_impossible_hijri_day_and_saves(): void
    {
        // Safar has 29 days: picking it clamps h_day 30 → 29, then saves.
        $screen = Native::test(RandevuCreate::class)
            ->set('title', 'Safar night')
            ->call('useHijri')
            ->set('h_day', '30')
            ->set('h_month', 'صفر')
            ->set('h_year', '1448')
            ->call('save');

        $screen->assertReplacedWith('/details/'.Randevu::where('title', 'Safar night')->firstOrFail()->id);

        $randevu = Randevu::where('title', 'Safar night')->firstOrFail();

        $this->assertSame([1448, 2, 29], [$randevu->hijri_year, $randevu->hijri_month, $randevu->hijri_day]);
    }

    public function test_follow_shows_hijri_date(): void
    {
        Randevu::create(array_merge(
            ['title' => 'Trip', 'occurs_on' => today()->addDays(3)],
            Randevu::hijriTriple(today()->addDays(3)->toDateString()),
            ['entered_in' => 'gregorian']
        ));

        Native::test(Follow::class)->assertSee(Randevu::firstOrFail()->hijriLabel());
    }

    public function test_settings_switches_language_and_persists(): void
    {
        $screen = Native::test(Settings::class)
            ->assertSet('locale', 'ar')
            ->assertSee('اللغة');

        $screen->call('useEnglish')
            ->assertSet('locale', 'en')
            ->assertSee('Language');

        $this->assertSame('en', Setting::get('locale'));

        // A fresh mount picks up the stored language without restart.
        Native::test(Follow::class)->assertSee('Add your first randevu');

        $screen->call('useArabic')->assertSet('locale', 'ar');
        $this->assertSame('ar', Setting::get('locale'));
    }

    public function test_settings_theme_switch_persists(): void
    {
        $screen = Native::test(Settings::class)
            ->assertSet('theme', 'light');

        $screen->press('useDark')
            ->assertSet('theme', 'dark');

        $this->assertSame('dark', Setting::get('theme'));

        // A fresh mount reflects the stored mode on the highlighted button.
        Native::test(Settings::class)
            ->assertSet('theme', 'dark')
            ->assertElement('button', fn ($n) => in_array($n['props']['label'] ?? '', ['Dark', 'غامق'], true)
                && ($n['props']['variant'] ?? '') === 'primary');

        $screen->press('useLight')
            ->assertSet('theme', 'light');

        $this->assertSame('light', Setting::get('theme'));
    }

    public function test_settings_buttons_switch_language_and_theme(): void
    {
        $screen = Native::test(Settings::class);

        $screen->press('useEnglish')
            ->assertSet('locale', 'en');

        $this->assertSame('en', Setting::get('locale'));

        $screen->press('useDark')
            ->assertSet('theme', 'dark');

        $this->assertSame('dark', Setting::get('theme'));

        $screen->press('useArabic')
            ->assertSet('locale', 'ar');

        $screen->press('useLight')
            ->assertSet('theme', 'light');
    }

    public function test_settings_option_buttons_render(): void
    {
        Setting::set('locale', 'en');

        $screen = Native::test(Settings::class);

        $screen->assertElement('button', fn ($n) => ($n['props']['label'] ?? '') === 'العربية');
        $screen->assertElement('button', fn ($n) => ($n['props']['label'] ?? '') === 'English');
        $screen->assertElement('button', fn ($n) => ($n['props']['label'] ?? '') === 'Light'
            && ($n['props']['variant'] ?? '') === 'primary');
        $screen->assertElement('button', fn ($n) => ($n['props']['label'] ?? '') === 'Dark'
            && ($n['props']['variant'] ?? '') === 'ghost');
        $screen->assertElement('divider', fn ($n) => true);
    }

    public function test_layout_mirrors_order_sensitive_rows_per_direction(): void
    {
        Randevu::create(['title' => 'Trip', 'occurs_on' => today()->addDay()]);

        // Arabic (RTL): mirrored DOM order. Labels hardcoded: the test
        // process locale is unreliable for __() here.
        $this->assertSame(
            ['السنة', 'الشهر', 'اليوم'],
            $this->selectRowLabels(Native::test(RandevuCreate::class)->tree())
        );
        $this->assertSame('English', $this->firstButtonLabel(Native::test(Settings::class)->tree()));
        $this->assertSame('column', $this->cardRowFirstType(Native::test(Follow::class)->tree()));

        // English (LTR): source order.
        Setting::set('locale', 'en');

        $this->assertSame(
            ['Day', 'Month', 'Year'],
            $this->selectRowLabels(Native::test(RandevuCreate::class)->tree())
        );
        $this->assertSame('العربية', $this->firstButtonLabel(Native::test(Settings::class)->tree()));
        $this->assertSame('webview', $this->cardRowFirstType(Native::test(Follow::class)->tree()));
    }

    public function test_time_row_keeps_narrow_order_per_direction(): void
    {
        // Deliberately NOT mirrored: hour-then-minute reads the same in
        // both directions. Labels hardcoded: the test process locale is
        // unreliable for __() here.
        $this->assertSame(
            ['الساعة', 'الدقيقة'],
            $this->selectRowLabels(Native::test(RandevuCreate::class)->tree(), 2)
        );

        // English (LTR): same order.
        Setting::set('locale', 'en');

        $this->assertSame(
            ['Hour', 'Minute'],
            $this->selectRowLabels(Native::test(RandevuCreate::class)->tree(), 2)
        );
    }

    public function test_period_chips_mirror_per_direction(): void
    {
        // Arabic (RTL): mirrored DOM order, hours first (rightmost). Labels
        // hardcoded: the test process locale is unreliable for __() here.
        $this->assertSame(
            ['ساعات', 'أيام', 'شهور', 'سنين'],
            $this->chipRowLabels(Native::test(RandevuCreate::class)->tree())
        );

        // English (LTR): source order.
        Setting::set('locale', 'en');

        $this->assertSame(
            ['Years', 'Months', 'Days', 'Hours'],
            $this->chipRowLabels(Native::test(RandevuCreate::class)->tree())
        );
    }

    /** Labels of the first row holding only chips (the period row). */
    private function chipRowLabels(array $tree): array
    {
        foreach ($this->collectNodes($tree, 'row') as $row) {
            $children = [];
            foreach ($row['children'] ?? [] as $child) {
                if (is_array($child)) {
                    $children[] = $child;
                }
            }

            $allChips = count($children) === 4;
            foreach ($children as $child) {
                $allChips = $allChips && (($child['type'] ?? null) === 'chip');
            }

            if ($allChips) {
                return array_map(fn ($child) => $child['props']['label'] ?? '', $children);
            }
        }

        $this->fail('No 4-chip row found in the rendered tree.');

        return [];
    }

    /** Labels of the first row holding exactly the given selects count. */
    private function selectRowLabels(array $tree, int $count = 3): array
    {
        foreach ($this->collectNodes($tree, 'row') as $row) {
            $children = [];
            foreach ($row['children'] ?? [] as $child) {
                if (is_array($child)) {
                    $children[] = $child;
                }
            }

            $allSelects = count($children) === $count;
            foreach ($children as $child) {
                $allSelects = $allSelects && (($child['type'] ?? null) === 'select');
            }

            if ($allSelects) {
                return array_map(fn ($child) => $child['props']['label'] ?? '', $children);
            }
        }

        $this->fail("No {$count}-select row found in the rendered tree.");

        return [];
    }

    /** Label of the first button in the first 2-button row. */
    private function firstButtonLabel(array $tree): string
    {
        foreach ($this->collectNodes($tree, 'row') as $row) {
            $children = [];
            foreach ($row['children'] ?? [] as $child) {
                if (is_array($child)) {
                    $children[] = $child;
                }
            }

            $allButtons = count($children) === 2;
            foreach ($children as $child) {
                $allButtons = $allButtons && (($child['type'] ?? null) === 'button');
            }

            if ($allButtons) {
                return $children[0]['props']['label'] ?? '';
            }
        }

        $this->fail('No 2-button row found in the rendered tree.');

        return '';
    }

    /** Type of the first child of the card row (holds the ring webview). */
    private function cardRowFirstType(array $tree): string
    {
        foreach ($this->collectNodes($tree, 'row') as $row) {
            $children = [];
            foreach ($row['children'] ?? [] as $child) {
                if (is_array($child)) {
                    $children[] = $child;
                }
            }

            $hasWebview = false;
            foreach ($children as $child) {
                $hasWebview = $hasWebview || (($child['type'] ?? null) === 'webview');
            }

            if ($hasWebview && count($children) > 0) {
                return $children[0]['type'];
            }
        }

        $this->fail('No card row found in the rendered tree.');

        return '';
    }

    /** @return list<array> */
    private function collectNodes(array $node, string $type): array
    {
        $out = [];

        if (($node['type'] ?? null) === $type) {
            $out[] = $node;
        }

        foreach ($node['children'] ?? [] as $child) {
            if (is_array($child)) {
                array_push($out, ...$this->collectNodes($child, $type));
            }
        }

        return $out;
    }

    public function test_default_locale_is_arabic(): void
    {
        $this->assertSame('ar', config('app.locale'));
        $this->assertSame('en', config('app.fallback_locale'));
    }

    public function test_follow_screen_route_answers(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_edit_route_resolves_via_visit(): void
    {
        $randevu = Randevu::create(['title' => 'Routed', 'occurs_on' => today()]);

        Native::visit('/edit/'.$randevu->id)
            ->assertSee('تعديل الميعاد')
            ->assertSet('title', 'Routed');
    }

    public function test_settings_route_resolves_via_visit(): void
    {
        Native::visit('/settings')->assertSee('اللغة');
    }

    public function test_follow_memories_settings_and_dashboard_scroll(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Soon', 'occurs_on' => today()->addDay()]);
        Randevu::create(['title' => 'Past', 'occurs_on' => today()->subDay()]);

        Native::test(Follow::class)->assertElement('scroll_view');
        Native::test(Memories::class)->assertElement('scroll_view');
        Native::test(Settings::class)->assertElement('scroll_view');
        Native::test(Dashboard::class)->assertElement('scroll_view');
    }

    public function test_follow_empty_state_stays_centered_without_scroll(): void
    {
        // No rows: the centered empty state renders directly — no scroll
        // container that would break its flex-1 centering.
        Native::test(Follow::class)->assertMissingElement('scroll_view');
        Native::test(Memories::class)->assertMissingElement('scroll_view');
    }

    public function test_follow_search_filters_by_title(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()->addDays(5)]);
        Randevu::create(['title' => 'Birthday', 'occurs_on' => today()->addDays(10)]);

        $screen = Native::test(Follow::class)
            ->assertSee('Search')
            ->assertSee('Nearest')
            ->assertSee('Newest')
            ->assertSee('A–Z');

        // One character is already enough to filter.
        $screen->set('search', 'b')->assertSee('Birthday')->assertDontSee('Dentist');
        $screen->set('search', 'dent')->assertSee('Dentist')->assertDontSee('Birthday');

        // Clearing the search restores the full list.
        $screen->set('search', '')->assertSee('Dentist')->assertSee('Birthday');
    }

    public function test_follow_search_no_matches_state_and_clear(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()->addDays(5)]);

        $screen = Native::test(Follow::class)
            ->set('search', 'zzz');

        $screen
            ->assertSee('No matches')
            ->assertSee('Try another word or clear the search')
            ->assertDontSee('Dentist')
            ->assertDontSee('Add your first randevu');

        $screen->press('clearSearch')->assertSee('Dentist')->assertDontSee('No matches');
    }

    public function test_follow_sort_orders_entries(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => 'Mango', 'occurs_on' => today()->addDays(30)]);
        Randevu::create(['title' => 'apple', 'occurs_on' => today()->addDays(5)]);
        Randevu::create(['title' => 'Zebra', 'occurs_on' => today()->addDays(10)]);

        $screen = Native::test(Follow::class);

        $titles = fn () => array_column($screen->get('appointments'), 'title');

        // Default: nearest first (the upcoming scope).
        $this->assertSame(['apple', 'Zebra', 'Mango'], $titles());

        $screen->press('sortByNewest');
        $this->assertSame(['Zebra', 'apple', 'Mango'], $titles());

        $screen->press('sortByAlpha');
        $this->assertSame(['apple', 'Mango', 'Zebra'], $titles());

        $screen->press('sortByNearest');
        $this->assertSame(['apple', 'Zebra', 'Mango'], $titles());
    }

    public function test_follow_search_matches_percent_literally(): void
    {
        Setting::set('locale', 'en');

        Randevu::create(['title' => '100% sure', 'occurs_on' => today()->addDays(5)]);
        Randevu::create(['title' => '1000 ideas', 'occurs_on' => today()->addDays(6)]);

        Native::test(Follow::class)
            ->set('search', '100%')
            ->assertSee('100% sure')
            ->assertDontSee('1000 ideas');
    }

    public function test_blank_search_on_empty_list_shows_first_run_state(): void
    {
        Setting::set('locale', 'en');

        Native::test(Follow::class)
            ->set('search', '   ')
            ->assertSee('Add your first randevu')
            ->assertDontSee('No matches');
    }

    public function test_memories_search_and_sort(): void
    {
        Setting::set('locale', 'en');

        // Newer date first on purpose: nearest and newest must disagree.
        Randevu::create(['title' => 'Mango', 'occurs_on' => today()->subDays(5)]);
        Randevu::create(['title' => 'apple', 'occurs_on' => today()->subDays(10)]);
        Randevu::create(['title' => 'Zebra', 'occurs_on' => today()->subDays(40)]);

        $screen = Native::test(Memories::class);

        $titles = fn () => array_column($screen->get('memories'), 'title');

        // Default: nearest to today first (the memories scope).
        $this->assertSame(['Mango', 'apple', 'Zebra'], $titles());

        $screen->set('search', 'app');
        $this->assertSame(['apple'], $titles());

        $screen->set('search', '');
        $screen->press('sortByNewest');
        $this->assertSame(['Zebra', 'apple', 'Mango'], $titles());

        $screen->press('sortByAlpha');
        $this->assertSame(['apple', 'Mango', 'Zebra'], $titles());
    }

    public function test_list_toolbar_renders_in_arabic(): void
    {
        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()]);
        Randevu::create(['title' => 'Old', 'occurs_on' => today()->subDay()]);

        Native::test(Follow::class)
            ->assertSee('دوّر')
            ->assertSee('الأقرب')
            ->assertSee('الأجدد')
            ->assertSee('أبجدي');

        Native::test(Memories::class)
            ->assertSee('دوّر')
            ->assertSee('الأقرب')
            ->assertSee('الأجدد')
            ->assertSee('أبجدي');
    }

    public function test_create_form_quick_set_chips_update_selects(): void
    {
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class)
            ->press('setPlus7');

        $date = today()->addDays(7);
        $screen
            ->assertSet('day', (string) $date->day)
            ->assertSet('month', RandevuTime::monthNames()[$date->month - 1])
            ->assertSet('year', (string) $date->year);

        [$hy, $hm, $hd] = RandevuHijri::fromGregorian($date->year, $date->month, $date->day);
        $screen
            ->assertSet('h_day', (string) $hd)
            ->assertSet('h_month', RandevuHijri::MONTH_NAMES[$hm - 1])
            ->assertSet('h_year', (string) $hy);

        $screen->press('setToday');
        $screen->assertSet('day', (string) today()->day);
    }

    public function test_edit_form_quick_set_chips_update_selects(): void
    {
        Setting::set('locale', 'en');

        $randevu = Randevu::create(['title' => 'Old', 'occurs_on' => today()->subDays(40)]);

        $screen = Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->press('setPlus30');

        $date = today()->addDays(30);
        $screen
            ->assertSet('day', (string) $date->day)
            ->assertSet('month', RandevuTime::monthNames()[$date->month - 1])
            ->assertSet('year', (string) $date->year);
    }

    public function test_follow_cards_use_token_radius(): void
    {
        Randevu::create(['title' => 'Dentist', 'occurs_on' => today()]);

        // rounded-2xl resolves to the 16pt token radius (not an ignored class).
        Native::test(Follow::class)
            ->assertElement('pressable', fn ($n) => (($n['style']['border_radius'] ?? null) == 16));
    }

    public function test_quick_set_chips_render_in_arabic(): void
    {
        Native::test(RandevuCreate::class)
            ->assertSee('النهاردة')
            ->assertSee('+7 أيام')
            ->assertSee('+30 يوم');

        $randevu = Randevu::create(['title' => 'Old', 'occurs_on' => today()]);
        Native::test(RandevuEdit::class, ['id' => $randevu->id])->assertSee('+30 يوم');
    }

    public function test_color_picker_custom_dot_has_a11y_label(): void
    {
        Setting::set('locale', 'en');

        Native::test(RandevuCreate::class)
            ->assertElement('pressable', fn ($n) => (($n['props']['a11y_label'] ?? null) === 'None'));

        Native::test(RandevuCreate::class)
            ->set('color', '#123456')
            ->assertElement('pressable', fn ($n) => (($n['props']['a11y_label'] ?? null) === 'Custom color'));
    }

    public function test_details_cover_has_a11y_label_and_alt(): void
    {
        Setting::set('locale', 'en');

        $tmp = tempnam(sys_get_temp_dir(), 'cover');
        file_put_contents($tmp, 'cover-bytes');
        $stored = CoverImage::store($tmp);

        $randevu = Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDays(5), 'cover_path' => $stored]);

        $screen = Native::test(RandevuDetails::class, ['id' => $randevu->id]);
        $screen->assertElement('pressable', fn ($n) => (($n['props']['a11y_label'] ?? null) === 'Open cover'));

        $screen->press('openCover');
        $screen->assertElement('image', fn ($n) => (($n['props']['alt'] ?? null) === 'Selected cover'));

        @unlink($tmp);
    }

    public function test_cover_picker_preview_has_alt(): void
    {
        Setting::set('locale', 'en');

        $tmp = tempnam(sys_get_temp_dir(), 'cover');
        file_put_contents($tmp, 'cover-bytes');
        $stored = CoverImage::store($tmp);

        Native::test(RandevuCreate::class)
            ->set('cover_path', $stored)
            ->assertElement('image', fn ($n) => (($n['props']['alt'] ?? null) === 'Selected cover'));

        @unlink($tmp);
    }

    public function test_a11y_labels_render_in_arabic(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'cover');
        file_put_contents($tmp, 'cover-bytes');
        $stored = CoverImage::store($tmp);

        $randevu = Randevu::create(['title' => 'Covered', 'occurs_on' => today()->addDays(5), 'cover_path' => $stored]);

        $screen = Native::test(RandevuDetails::class, ['id' => $randevu->id]);
        $screen->assertElement('pressable', fn ($n) => (($n['props']['a11y_label'] ?? null) === 'افتح الغلاف'));

        $screen->press('openCover');
        $screen->assertElement('image', fn ($n) => (($n['props']['alt'] ?? null) === 'الغلاف المختار'));

        Native::test(RandevuCreate::class)
            ->assertElement('pressable', fn ($n) => (($n['props']['a11y_label'] ?? null) === 'بدون'));

        Native::test(RandevuCreate::class)
            ->set('color', '#123456')
            ->assertElement('pressable', fn ($n) => (($n['props']['a11y_label'] ?? null) === 'لون مخصص'));

        @unlink($tmp);
    }
}
