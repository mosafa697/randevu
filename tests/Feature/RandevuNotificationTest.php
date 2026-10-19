<?php

namespace Tests\Feature;

use App\Models\Randevu;
use App\Models\Setting;
use App\NativeComponents\RandevuCreate;
use App\NativeComponents\RandevuEdit;
use App\NativeComponents\Settings;
use App\Services\RandevuNotificationBridge;
use App\Services\RandevuNotificationRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Native\Mobile\Platform;
use Native\Mobile\Testing\Native;
use Tests\TestCase;

class RandevuNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_entry_notification_defaults_are_off(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');

        $screen = Native::test(RandevuCreate::class);

        $this->assertSame(
            RandevuNotificationRules::reminderLabel('off'),
            $screen->get('reminderSelection')
        );
        $this->assertSame(
            RandevuNotificationRules::anniversaryLabel('none'),
            $screen->get('anniversaryModeSelection')
        );
    }

    public function test_create_persists_notification_rules(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');

        Native::test(RandevuCreate::class)
            ->set('title', 'Notification test')
            ->set('reminderSelection', RandevuNotificationRules::reminderLabel('1d'))
            ->set('anniversaryModeSelection', RandevuNotificationRules::anniversaryLabel('yearly'))
            ->set('anniversaryLeadSelection', RandevuNotificationRules::leadLabel(7))
            ->call('save');

        $randevu = Randevu::where('title', 'Notification test')->firstOrFail();

        $this->assertSame('1d', $randevu->reminder_rule);
        $this->assertSame('yearly', $randevu->anniversary_mode);
        $this->assertSame(7, $randevu->anniversary_lead_days);
    }

    public function test_edit_updates_notification_rules(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');

        $randevu = Randevu::create([
            'title' => 'Editable reminder',
            'occurs_on' => today(),
            'reminder_rule' => '15m',
            'anniversary_mode' => 'none',
            'anniversary_lead_days' => 0,
        ]);

        Native::test(RandevuEdit::class, ['id' => $randevu->id])
            ->set('reminderSelection', RandevuNotificationRules::reminderLabel('1h'))
            ->set('anniversaryModeSelection', RandevuNotificationRules::anniversaryLabel('both'))
            ->set('anniversaryLeadSelection', RandevuNotificationRules::leadLabel(1))
            ->call('update');

        $randevu->refresh();

        $this->assertSame('1h', $randevu->reminder_rule);
        $this->assertSame('both', $randevu->anniversary_mode);
        $this->assertSame(1, $randevu->anniversary_lead_days);
    }

    public function test_notification_master_toggle_persists_and_renders_status(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');

        $screen = Native::test(Settings::class);
        $this->assertTrue($screen->get('notificationsEnabled'));

        $screen->press('disableNotifications');

        $this->assertFalse($screen->get('notificationsEnabled'));
        $this->assertSame('0', Setting::get('notifications_enabled'));
    }

    public function test_notification_permission_action_is_hidden_and_safe_when_bridge_is_unavailable(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');

        $screen = Native::test(Settings::class)
            ->assertDontSee(__('randevu.notification_request_permission'));

        $this->assertFalse($screen->get('notificationsAvailable'));
        $screen->call('requestNotificationPermission');
        $this->assertSame(__('randevu.notification_not_available'), $screen->get('notificationStatus'));
    }

    public function test_native_notification_bridge_is_unavailable_to_console_commands(): void
    {
        Platform::set(Platform::ANDROID);

        try {
            $this->assertFalse(app(RandevuNotificationBridge::class)->available());
        } finally {
            Platform::set(null);
        }
    }
}
