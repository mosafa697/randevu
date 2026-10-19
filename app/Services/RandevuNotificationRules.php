<?php

namespace App\Services;

use App\Models\Randevu;
use App\Models\Setting;

class RandevuNotificationRules
{
    /** @var array<string,string> */
    private const REMINDER_TRANSLATIONS = [
        'off' => 'notification_off',
        'at_time' => 'reminder_at_time',
        '15m' => 'reminder_15_minutes',
        '1h' => 'reminder_1_hour',
        'morning_of' => 'reminder_morning_of',
        '1d' => 'reminder_1_day',
        '2d' => 'reminder_2_days',
        '1w' => 'reminder_1_week',
    ];

    /** @var array<string,string> */
    private const ANNIVERSARY_TRANSLATIONS = [
        'none' => 'anniversary_off',
        'yearly' => 'anniversary_yearly',
        'monthly' => 'anniversary_monthly',
        'both' => 'anniversary_both',
    ];

    /** @var array<int,string> */
    private const LEAD_TRANSLATIONS = [
        0 => 'anniversary_on_day',
        1 => 'anniversary_1_day',
        7 => 'anniversary_1_week',
    ];

    /** @return list<string> */
    public static function reminderOptions(): array
    {
        return array_map(
            static fn (string $key): string => __('randevu.'.$key),
            array_values(self::REMINDER_TRANSLATIONS)
        );
    }

    /** @return list<string> */
    public static function anniversaryOptions(): array
    {
        return array_map(
            static fn (string $key): string => __('randevu.'.$key),
            array_values(self::ANNIVERSARY_TRANSLATIONS)
        );
    }

    /** @return list<string> */
    public static function leadOptions(): array
    {
        return array_map(
            static fn (string $key): string => __('randevu.'.$key),
            array_values(self::LEAD_TRANSLATIONS)
        );
    }

    public static function reminderLabel(string $rule): string
    {
        return __('randevu.'.(self::REMINDER_TRANSLATIONS[$rule] ?? self::REMINDER_TRANSLATIONS['off']));
    }

    public static function anniversaryLabel(string $mode): string
    {
        return __('randevu.'.(self::ANNIVERSARY_TRANSLATIONS[$mode] ?? self::ANNIVERSARY_TRANSLATIONS['none']));
    }

    public static function leadLabel(int $days): string
    {
        return __('randevu.'.(self::LEAD_TRANSLATIONS[$days] ?? self::LEAD_TRANSLATIONS[0]));
    }

    public static function reminderCode(string $label): ?string
    {
        return self::codeForLabel($label, self::REMINDER_TRANSLATIONS);
    }

    public static function anniversaryCode(string $label): ?string
    {
        return self::codeForLabel($label, self::ANNIVERSARY_TRANSLATIONS);
    }

    public static function leadDays(string $label): ?int
    {
        foreach (self::LEAD_TRANSLATIONS as $days => $translation) {
            if ($label === __('randevu.'.$translation)) {
                return $days;
            }
        }

        return null;
    }

    public static function isArmed(Randevu $randevu): bool
    {
        if (Setting::get('notifications_enabled', '1') === '0') {
            return false;
        }

        return ($randevu->isAppointment() && $randevu->reminder_rule !== 'off')
            || $randevu->anniversary_mode !== 'none';
    }

    public static function description(Randevu $randevu): string
    {
        if (Setting::get('notifications_enabled', '1') === '0') {
            return __('randevu.notifications_master_off');
        }

        $descriptions = [];

        if ($randevu->isAppointment() && $randevu->reminder_rule !== 'off') {
            $descriptions[] = __('randevu.armed_reminder', [
                'rule' => self::reminderLabel($randevu->reminder_rule),
            ]);
        }

        if ($randevu->anniversary_mode !== 'none') {
            $descriptions[] = __('randevu.armed_anniversary', [
                'mode' => self::anniversaryLabel($randevu->anniversary_mode),
                'lead' => self::leadLabel($randevu->anniversary_lead_days),
            ]);
        }

        return $descriptions === []
            ? __('randevu.notification_off')
            : implode(__('randevu.notification_body_separator'), $descriptions);
    }

    /** @param array<string,string> $translations */
    private static function codeForLabel(string $label, array $translations): ?string
    {
        foreach ($translations as $code => $translation) {
            if ($label === __('randevu.'.$translation)) {
                return $code;
            }
        }

        return null;
    }
}
