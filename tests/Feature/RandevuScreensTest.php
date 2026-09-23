<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\NativeComponents\Follow;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuEdit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

class RandevuScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_shows_empty_state(): void
    {
        Native::test(Follow::class)
            ->assertSee('No randevus yet')
            ->assertSee('Add your first randevu');
    }

    public function test_follow_sections_today_upcoming_and_memories(): void
    {
        Randevu::create(['title' => 'Dentist', 'occurs_on' => today(), 'note' => 'Bring card']);
        Randevu::create(['title' => 'Trip', 'occurs_on' => today()->addDays(3)]);
        Randevu::create(['title' => 'Graduation', 'occurs_on' => today()->subDays(2)]);

        Native::test(Follow::class)
            ->assertSee('Today')
            ->assertSee('Coming up')
            ->assertSee('Memories')
            ->assertSee('Dentist')
            ->assertSee('Trip')
            ->assertSee('Graduation')
            ->assertSee('In 3 days')
            ->assertSee('2 days ago')
            ->assertSee('Bring card');
    }

    public function test_create_rejects_invalid_input_and_keeps_values(): void
    {
        $screen = Native::test(RandevuCreate::class)
            ->set('title', '')
            ->set('occurs_on', 'not-a-date')
            ->call('save');

        $screen->assertNotSet('errors', []);
        $this->assertSame('', $screen->get('title'));
        $this->assertSame('not-a-date', $screen->get('occurs_on'));
        $this->assertDatabaseMissing('randevus', ['occurs_on' => 'not-a-date']);
        $this->assertDatabaseCount('randevus', 0);
    }

    public function test_create_saves_and_returns_to_follow(): void
    {
        Native::test(RandevuCreate::class)
            ->set('title', 'Dentist')
            ->set('occurs_on', today()->addDay()->toDateString())
            ->set('note', 'Second floor')
            ->call('save')
            ->assertReplacedWith('/');

        $this->assertDatabaseHas('randevus', ['title' => 'Dentist', 'note' => 'Second floor']);
    }

    public function test_edit_prefills_and_saves(): void
    {
        $randevu = Randevu::create(['title' => 'Old', 'occurs_on' => today(), 'note' => 'x']);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->assertSet('title', 'Old')
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
            ->assertSee('Delete this randevu?');

        $screen->call('destroy')->assertReplacedWith('/');

        $this->assertDatabaseMissing('randevus', ['id' => $randevu->id]);
    }

    public function test_follow_screen_route_answers(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_edit_route_resolves_via_visit(): void
    {
        $randevu = Randevu::create(['title' => 'Routed', 'occurs_on' => today()]);

        Native::visit('/edit/'.$randevu->id)
            ->assertSee('Edit randevu')
            ->assertSet('title', 'Routed');
    }
}
