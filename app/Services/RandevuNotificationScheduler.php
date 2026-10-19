<?php

namespace App\Services;

use App\Models\Randevu;
use App\Models\Setting;
use App\Support\Bidi;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class RandevuNotificationScheduler
{
    /** @var array<string,int> */
    private const REMINDER_MINUTES = [
        '15m' => 15,
        '1h' => 60,
        '2d' => 2880,
        '1w' => 10080,
    ];

    public function __construct(private readonly RandevuNotificationBridge $bridge) {}

    public function sync(): void
    {
        if (! $this->bridge->available()) {
            return;
        }

        $status = $this->bridge->status();

        if (! $status['available']) {
            return;
        }

        $timezone = $status['timezone'];

        if ($timezone === null || $timezone === '') {
            throw new \RuntimeException('The Android notification bridge did not provide the device time zone.');
        }

        $this->bridge->sync($this->buildSchedules(Carbon::now($timezone)));
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function buildSchedules(?CarbonInterface $now = null): array
    {
        if (Setting::get('notifications_enabled', '1') === '0') {
            return [];
        }

        $now = $now === null ? Carbon::now() : Carbon::instance($now);
        $schedules = [];

        foreach (Randevu::query()->orderBy('id')->get() as $randevu) {
            $isAppointment = $randevu->occurs_on->toDateString() >= $now->toDateString();

            if ($isAppointment) {
                $schedule = $this->appointmentSchedule($randevu, $now);

                if ($schedule !== null) {
                    $schedules[] = $schedule;
                }
            }

            foreach ($this->anniversaryModes($randevu->anniversary_mode) as $mode) {
                $schedules[] = $this->anniversarySchedule($randevu, $mode, $now);
            }
        }

        return $schedules;
    }

    /** @return array<string,mixed>|null */
    private function appointmentSchedule(Randevu $randevu, Carbon $now): ?array
    {
        $rule = $randevu->reminder_rule;

        if ($rule === 'off') {
            return null;
        }

        $date = Carbon::parse($randevu->occurs_on->toDateString(), $now->timezone);
        $time = $randevu->occurs_time?->format('H:i') ?? '09:00';
        [$hour, $minute] = array_map(intval(...), explode(':', $time));
        $eventAt = $date->copy()->setTime($hour, $minute);

        $fireAt = match ($rule) {
            'at_time' => $eventAt,
            'morning_of' => $date->copy()->setTime(9, 0),
            '1d' => $date->copy()->subDay()->setTime(9, 0),
            default => isset(self::REMINDER_MINUTES[$rule])
                ? $eventAt->copy()->subMinutes(self::REMINDER_MINUTES[$rule])
                : null,
        };

        if ($fireAt === null || ! $fireAt->isAfter($now)) {
            return null;
        }

        if ($rule === 'morning_of' && $fireAt->greaterThan($eventAt)) {
            return null;
        }

        $phrase = RandevuTime::phraseFor(
            $randevu->occurs_on,
            $randevu->show_years,
            $randevu->show_months,
            $randevu->show_days,
            $randevu->show_hours,
            $randevu->occurs_time,
            $fireAt,
        );

        return [
            'id' => 'appointment-'.$randevu->id,
            'fireAtMillis' => $fireAt->getTimestamp() * 1000,
            'eventAtMillis' => $eventAt->getTimestamp() * 1000,
            'title' => Bidi::isolate($randevu->title),
            'body' => $this->body(
                $phrase,
                RandevuTime::absolute($randevu->occurs_on),
                $randevu->hijriLabel(),
            ),
            'route' => '/details/'.$randevu->id,
            'color' => $randevu->color,
            'channelName' => __('randevu.notifications_channel'),
            'repeat' => 'none',
            'eventDate' => $randevu->occurs_on->toDateString(),
            'eventTime' => $time,
            'reminderRule' => $rule,
        ];
    }

    /** @return array<string,mixed> */
    private function anniversarySchedule(Randevu $randevu, string $mode, Carbon $now): array
    {
        $leadDays = $randevu->anniversary_lead_days;
        $occurrence = $this->nextAnniversary($randevu, $mode, $leadDays, $now);
        $age = $mode === 'yearly'
            ? $occurrence['calendar_year'] - $occurrence['base_year']
            : (($occurrence['calendar_year'] - $occurrence['base_year']) * 12)
                + ($occurrence['calendar_month'] - $occurrence['base_month']);
        $age = max(1, $age);
        $unit = $mode === 'yearly' ? 'year' : 'month';
        $content = $this->anniversaryContent($randevu, $unit);

        return [
            'id' => 'anniversary-'.$randevu->id.'-'.$mode,
            'fireAtMillis' => $occurrence['fire_at']->getTimestamp() * 1000,
            'title' => Bidi::isolate($randevu->title),
            'body' => $this->body(
                RandevuTime::ago($unit, $age),
                RandevuTime::absolute($randevu->occurs_on),
                $randevu->hijriLabel(),
            ),
            'route' => '/details/'.$randevu->id,
            'color' => $randevu->color,
            'channelName' => __('randevu.notifications_channel'),
            'repeat' => $mode,
            'calendar' => $randevu->entered_in === 'hijri' ? 'hijri' : 'gregorian',
            'baseYear' => $occurrence['base_year'],
            'baseMonth' => $occurrence['base_month'],
            'baseDay' => $occurrence['base_day'],
            'eventTime' => $randevu->occurs_time?->format('H:i') ?? '09:00',
            'leadDays' => $leadDays,
            'content' => $content,
        ];
    }

    /**
     * @return array{
     *   event_date:Carbon,fire_at:Carbon,calendar_year:int,calendar_month:int,
     *   base_year:int,base_month:int,base_day:int
     * }
     */
    private function nextAnniversary(Randevu $randevu, string $mode, int $leadDays, Carbon $now): array
    {
        [$baseYear, $baseMonth, $baseDay] = $this->calendarDate($randevu);
        $time = $randevu->occurs_time?->format('H:i') ?? '09:00';
        [$hour, $minute] = array_map(intval(...), explode(':', $time));
        $enteredInHijri = $randevu->entered_in === 'hijri';

        if ($enteredInHijri) {
            [$currentYear, $currentMonth] = RandevuHijri::fromGregorian($now->year, $now->month, $now->day);
        } else {
            $currentYear = $now->year;
            $currentMonth = $now->month;
        }

        $attempts = $mode === 'yearly' ? 6 : 36;

        for ($offset = 0; $offset < $attempts; $offset++) {
            if ($enteredInHijri) {
                if ($mode === 'yearly') {
                    $year = max($currentYear, $baseYear + 1) + $offset;
                    $month = $baseMonth;
                } else {
                    $currentTotalMonths = ($currentYear * 12) + ($currentMonth - 1);
                    $baseTotalMonths = ($baseYear * 12) + ($baseMonth - 1);
                    $totalMonths = max($currentTotalMonths, $baseTotalMonths + 1) + $offset;
                    $year = intdiv($totalMonths, 12);
                    $month = ($totalMonths % 12) + 1;
                }

                $day = min($baseDay, RandevuHijri::daysInMonth($year, $month));
                [$gregorianYear, $gregorianMonth, $gregorianDay] = RandevuHijri::toGregorian($year, $month, $day);
            } else {
                if ($mode === 'yearly') {
                    $year = max($now->year, $baseYear + 1) + $offset;
                    $month = $baseMonth;
                } else {
                    $currentTotalMonths = ($now->year * 12) + ($now->month - 1);
                    $baseTotalMonths = ($baseYear * 12) + ($baseMonth - 1);
                    $totalMonths = max($currentTotalMonths, $baseTotalMonths + 1) + $offset;
                    $year = intdiv($totalMonths, 12);
                    $month = ($totalMonths % 12) + 1;
                }

                $day = min($baseDay, Carbon::create($year, $month, 1)->daysInMonth);
                $gregorianYear = $year;
                $gregorianMonth = $month;
                $gregorianDay = $day;
            }

            $eventDate = Carbon::create($gregorianYear, $gregorianMonth, $gregorianDay, 0, 0, 0, $now->timezone);
            $fireAt = $eventDate->copy()->setTime($hour, $minute)->subDays($leadDays);

            if ($fireAt->isAfter($now)) {
                return [
                    'event_date' => $eventDate,
                    'fire_at' => $fireAt,
                    'calendar_year' => $year,
                    'calendar_month' => $month,
                    'base_year' => $baseYear,
                    'base_month' => $baseMonth,
                    'base_day' => $baseDay,
                ];
            }
        }

        throw new \RuntimeException("Could not find a future {$mode} anniversary for randevu {$randevu->id}.");
    }

    /** @return array{int,int,int} */
    private function calendarDate(Randevu $randevu): array
    {
        if ($randevu->entered_in === 'hijri'
            && $randevu->hijri_year !== null
            && $randevu->hijri_month !== null
            && $randevu->hijri_day !== null) {
            return [$randevu->hijri_year, $randevu->hijri_month, $randevu->hijri_day];
        }

        return [$randevu->occurs_on->year, $randevu->occurs_on->month, $randevu->occurs_on->day];
    }

    /** @return list<string> */
    private function anniversaryModes(string $mode): array
    {
        return match ($mode) {
            'yearly' => ['yearly'],
            'monthly' => ['monthly'],
            'both' => ['yearly', 'monthly'],
            default => [],
        };
    }

    /** @return array<string,string|bool> */
    private function anniversaryContent(Randevu $randevu, string $unit): array
    {
        return [
            'ageTemplate' => __('randevu.'.$unit.'s_ago'),
            'nounOne' => __('randevu.'.$unit.'_one'),
            'nounTwo' => __('randevu.'.$unit.'_two'),
            'nounFew' => __('randevu.'.$unit.'_few'),
            'nounMany' => __('randevu.'.$unit.'_many'),
            'isArabic' => AppLocale::isArabic(),
            'separator' => __('randevu.notification_body_separator'),
            'absolute' => RandevuTime::absolute($randevu->occurs_on),
            'hijri' => $this->hijriLine($randevu->hijriLabel()),
        ];
    }

    private function body(string $phrase, string $absolute, ?string $hijri): string
    {
        $parts = [$phrase, $absolute];

        if ($hijri !== null) {
            $parts[] = __('randevu.notification_hijri_line', [
                'date' => $hijri.' '.RandevuHijri::hijriSuffix(),
            ]);
        }

        return implode(__('randevu.notification_body_separator'), $parts);
    }

    private function hijriLine(?string $hijri): string
    {
        return $hijri === null
            ? ''
            : __('randevu.notification_hijri_line', ['date' => $hijri.' '.RandevuHijri::hijriSuffix()]);
    }
}
