<?php

namespace App\NativeComponents;

use App\Models\Setting;
use App\NativeComponents\Concerns\AppliesLocale;
use App\NativeComponents\Concerns\AppliesTheme;
use App\Services\AppLocale;
use App\Services\AppTheme;
use App\Services\RandevuNotificationBridge;
use App\Services\RandevuNotificationRules;
use App\Services\RandevuNotificationScheduler;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Settings extends NativeComponent
{
    use AppliesLocale;
    use AppliesTheme;

    public string $locale = 'ar';

    public bool $notificationsEnabled = true;

    public bool $notificationsAvailable = false;

    public string $reminderDefaultSelection = '';

    public string $anniversaryDefaultSelection = '';

    public string $notificationStatus = '';

    public string $exactAlarmStatus = '';

    /** @var list<string> */
    public array $reminderOptions = [];

    /** @var list<string> */
    public array $anniversaryOptions = [];

    public function mount(): void
    {
        $this->applyLocale();
        $this->applyTheme();
        $this->locale = $this->currentLocale();
        $this->reminderOptions = RandevuNotificationRules::reminderOptions();
        $this->anniversaryOptions = RandevuNotificationRules::anniversaryOptions();
        $this->notificationsEnabled = Setting::get('notifications_enabled', '1') !== '0';
        $this->reminderDefaultSelection = RandevuNotificationRules::reminderLabel(Setting::get('reminder_default', 'off') ?? 'off');
        $this->anniversaryDefaultSelection = RandevuNotificationRules::anniversaryLabel(Setting::get('anniversary_default', 'none') ?? 'none');
        $this->refreshNotificationStatus();
        app(RandevuNotificationScheduler::class)->sync();
    }

    public function navTitle(): string
    {
        return __('randevu.settings_title');
    }

    public function appVersion(): string
    {
        return '1.0.0';
    }

    public function useArabic(): void
    {
        $this->locale = AppLocale::persist('ar');
        app(RandevuNotificationScheduler::class)->sync();
    }

    public function useFusha(): void
    {
        $this->locale = AppLocale::persist('ar_fusha');
        app(RandevuNotificationScheduler::class)->sync();
    }

    public function useEnglish(): void
    {
        $this->locale = AppLocale::persist('en');
        app(RandevuNotificationScheduler::class)->sync();
    }

    public function useLight(): void
    {
        $this->theme = AppTheme::persist('light');
    }

    public function useDark(): void
    {
        $this->theme = AppTheme::persist('dark');
    }

    public function enableNotifications(): void
    {
        Setting::set('notifications_enabled', '1');
        $this->notificationsEnabled = true;
        app(RandevuNotificationScheduler::class)->sync();
    }

    public function disableNotifications(): void
    {
        Setting::set('notifications_enabled', '0');
        $this->notificationsEnabled = false;
        app(RandevuNotificationScheduler::class)->sync();
    }

    public function saveNotificationDefaults(): void
    {
        $reminder = RandevuNotificationRules::reminderCode($this->reminderDefaultSelection);
        $anniversary = RandevuNotificationRules::anniversaryCode($this->anniversaryDefaultSelection);

        if ($reminder === null || $anniversary === null) {
            throw new \InvalidArgumentException('The selected notification default is invalid.');
        }

        Setting::set('reminder_default', $reminder);
        Setting::set('anniversary_default', $anniversary);
    }

    public function requestNotificationPermission(): void
    {
        $bridge = app(RandevuNotificationBridge::class);

        if (! $bridge->available()) {
            $this->refreshNotificationStatus();

            return;
        }

        $bridge->requestPermission();
        $this->refreshNotificationStatus();
    }

    public function openExactAlarmSettings(): void
    {
        app(RandevuNotificationBridge::class)->openExactAlarmSettings();
    }

    private function refreshNotificationStatus(): void
    {
        $status = app(RandevuNotificationBridge::class)->status();
        $this->notificationsAvailable = $status['available'];

        if (! $status['available']) {
            $this->notificationStatus = __('randevu.notification_not_available');
            $this->exactAlarmStatus = '';

            return;
        }

        $this->notificationStatus = __($status['notifications_granted']
            ? 'randevu.notification_permission_granted'
            : 'randevu.notification_permission_denied');
        $this->exactAlarmStatus = __($status['exact_alarms_granted']
            ? 'randevu.notification_exact_granted'
            : 'randevu.notification_exact_inexact');
    }

    public function render(): View
    {
        return view('native.settings');
    }
}
