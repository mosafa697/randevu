<?php

namespace Tests\Feature;

use App\Models\Randevu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RandevuTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_or_later_is_appointment_earlier_is_memory(): void
    {
        $appointment = Randevu::create(['title' => 'Dentist', 'occurs_on' => today()->addDay()]);
        $today = Randevu::create(['title' => 'Today thing', 'occurs_on' => today()]);
        $memory = Randevu::create(['title' => 'Graduation', 'occurs_on' => today()->subDay()]);

        $this->assertTrue($appointment->isAppointment());
        $this->assertTrue($today->isAppointment());
        $this->assertTrue($memory->isMemory());
        $this->assertFalse($memory->isAppointment());
    }

    public function test_upcoming_is_soonest_first_and_memories_newest_first(): void
    {
        Randevu::create(['title' => 'Far', 'occurs_on' => today()->addDays(10)]);
        Randevu::create(['title' => 'Near', 'occurs_on' => today()->addDays(2)]);
        Randevu::create(['title' => 'Old', 'occurs_on' => today()->subDays(10)]);
        Randevu::create(['title' => 'Recent', 'occurs_on' => today()->subDays(2)]);

        $this->assertSame(['Near', 'Far'], Randevu::upcoming()->pluck('title')->all());
        $this->assertSame(['Recent', 'Old'], Randevu::memories()->pluck('title')->all());
    }

    public function test_relative_phrase_and_exact_count_available(): void
    {
        $randevu = Randevu::create(['title' => 'Trip', 'occurs_on' => today()->addDays(3)]);

        $this->assertSame('In 3 days', $randevu->relativePhrase());
        $this->assertSame(3, $randevu->exactDayCount());
    }

    public function test_validation_rejects_invalid_input(): void
    {
        $validator = Validator::make(['title' => '', 'occurs_on' => 'not-a-date'], Randevu::rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->messages());
        $this->assertArrayHasKey('occurs_on', $validator->errors()->messages());
    }

    public function test_randevu_can_be_updated_and_deleted(): void
    {
        $randevu = Randevu::create(['title' => 'Old title', 'occurs_on' => today()]);

        $randevu->update(['title' => 'New title']);

        $this->assertSame('New title', $randevu->fresh()->title);

        $randevu->delete();

        $this->assertNull(Randevu::find($randevu->id));
    }
}
