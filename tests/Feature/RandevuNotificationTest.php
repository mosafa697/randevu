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
        $this->mockNotificationBridge(false, false, false);

        $screen = Native::test(Settings::class)
            ->assertDontSee(__('randevu.notification_request_permission'))
            ->assertDontSee(__('randevu.notification_permission_denied'))
            ->assertDontSee(__('randevu.notification_exact_inexact'));

        $this->assertFalse($screen->get('notificationsAvailable'));
        $screen->call('requestNotificationPermission');
        $this->assertFalse($screen->get('notificationPermissionDenied'));
    }

    public function test_denied_notification_permission_shows_guidance_and_request_action(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');
        $this->mockNotificationBridge(true, false, false);

        Native::test(Settings::class, platform: 'android')
            ->assertSee(__('randevu.notification_permission_denied'))
            ->assertSee(__('randevu.notification_request_permission'))
            ->assertDontSee(__('randevu.notification_exact_inexact'))
            ->assertDontSee(__('randevu.notification_open_exact_settings'));
    }

    public function test_notification_toggle_buttons_are_concise_and_have_icons(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');
        Setting::set('notifications_enabled', '1');
        $this->mockNotificationBridge(true, true, true);

        Native::test(Settings::class, platform: 'android')
            ->assertElement('button', fn ($node) => ($node['props']['label'] ?? '') === __('randevu.notifications_disable')
                && ! empty($node['props']['leading_icon']));
    }

    public function test_granted_notification_permission_has_no_permission_status_message(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');
        $this->mockNotificationBridge(true, true, true);

        Native::test(Settings::class)
            ->assertDontSee(__('randevu.notification_permission_denied'))
            ->assertDontSee(__('randevu.notification_exact_inexact'))
            ->assertDontSee(__('randevu.notification_request_permission'))
            ->assertDontSee(__('randevu.notification_open_exact_settings'));
    }

    public function test_exact_alarm_guidance_only_appears_when_permission_is_needed(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');
        $this->mockNotificationBridge(true, true, false);

        Native::test(Settings::class)
            ->assertDontSee(__('randevu.notification_permission_denied'))
            ->assertSee(__('randevu.notification_exact_inexact'))
            ->assertSee(__('randevu.notification_open_exact_settings'));
    }

    public function test_notification_defaults_are_saved_as_each_selection_changes(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');
        $this->mockNotificationBridge(false, false, false);

        $screen = Native::test(Settings::class);
        $screen->set('reminderDefaultSelection', RandevuNotificationRules::reminderLabel('1d'));

        $this->assertSame('1d', Setting::get('reminder_default'));
        $this->assertNull(Setting::get('anniversary_default'));

        $screen->set('anniversaryDefaultSelection', RandevuNotificationRules::anniversaryLabel('yearly'));

        $this->assertSame('yearly', Setting::get('anniversary_default'));
    }

    public function test_invalid_notification_default_selection_is_rejected(): void
    {
        App::setLocale('en');
        Setting::set('locale', 'en');
        $this->mockNotificationBridge(false, false, false);

        $screen = Native::test(Settings::class);

        $this->expectException(\InvalidArgumentException::class);
        $screen->set('reminderDefaultSelection', 'Invalid reminder');
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

    private function mockNotificationBridge(bool $available, bool $notificationsGranted, bool $exactAlarmsGranted): void
    {
        $status = [
            'available' => $available,
            'notifications_granted' => $notificationsGranted,
            'exact_alarms_granted' => $exactAlarmsGranted,
            'timezone' => 'UTC',
        ];
        $bridge = \Mockery::mock(RandevuNotificationBridge::class);
        $bridge->shouldReceive('available')->andReturn($available);
        $bridge->shouldReceive('status')->andReturn($status);

        if ($available) {
            $bridge->shouldReceive('sync')->once();
        }

        $this->app->instance(RandevuNotificationBridge::class, $bridge);
    }
}
