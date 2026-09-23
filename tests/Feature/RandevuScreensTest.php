<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\Follow;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuEdit;
use App\NativeComponents\Settings;
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
            ->assertDontSee('مفيش مواعيد لسه')
            ->assertDontSee('عشان تتابعها');
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
            ->assertDontSee('مفيش مواعيد لسه');
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
        $this->assertSame('30', $screen->get('day'));
        $this->assertDatabaseCount('randevus', 0);
    }

    public function test_create_saves_and_returns_to_follow(): void
    {
        $date = today()->addDay();

        Native::test(RandevuCreate::class)
            ->set('title', 'Dentist')
            ->set('day', (string) $date->day)
            ->set('month', \App\Services\RandevuTime::monthNames()[$date->month - 1])
            ->set('year', (string) $date->year)
            ->set('note', 'Second floor')
            ->call('save')
            ->assertReplacedWith('/');

        $this->assertDatabaseHas('randevus', ['title' => 'Dentist', 'note' => 'Second floor']);
        $this->assertSame(
            $date->toDateString(),
            Randevu::where('title', 'Dentist')->firstOrFail()->occurs_on->toDateString()
        );
    }

    public function test_edit_prefills_and_saves(): void
    {
        $randevu = Randevu::create(['title' => 'Old', 'occurs_on' => today(), 'note' => 'x']);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('title', 'Old')
            ->assertSet('month', \App\Services\RandevuTime::monthNames()[today()->month - 1])
            ->set('title', 'New')
            ->call('update')
            ->assertReplacedWith('/');

        $this->assertSame('New', $randevu->fresh()->title);
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

        $screen->call('destroy')->assertReplacedWith('/');

        $this->assertDatabaseMissing('randevus', ['id' => $randevu->id]);
    }

    public function test_create_in_hijri_mode_stores_both_calendars(): void
    {
        Native::test(RandevuCreate::class)
            ->set('title', 'Ramadan night')
            ->call('useHijri')
            ->set('h_day', '10')
            ->set('h_month', 'ربيع الثاني')
            ->set('h_year', '1448')
            ->call('save')
            ->assertReplacedWith('/');

        $randevu = Randevu::where('title', 'Ramadan night')->firstOrFail();

        $this->assertSame('2026-09-23', $randevu->occurs_on->toDateString());
        $this->assertSame([1448, 4, 10], [$randevu->hijri_year, $randevu->hijri_month, $randevu->hijri_day]);
        $this->assertSame('hijri', $randevu->entered_in);
    }

    public function test_create_rejects_impossible_hijri_date(): void
    {
        Native::test(RandevuCreate::class)
            ->set('title', 'Bad hijri')
            ->call('useHijri')
            ->set('h_day', '30')
            ->set('h_month', 'صفر')
            ->set('h_year', '1448')
            ->call('save')
            ->assertNotSet('errors', []);

        $this->assertDatabaseCount('randevus', 0);
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
}
