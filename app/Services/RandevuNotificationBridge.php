<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Native\Mobile\Platform;
use RuntimeException;

class RandevuNotificationBridge
{
    /** @return array{available:bool,notifications_granted:bool,exact_alarms_granted:bool,timezone:?string} */
    public function status(): array
    {
        if (! $this->available()) {
            return [
                'available' => false,
                'notifications_granted' => false,
                'exact_alarms_granted' => false,
                'timezone' => null,
            ];
        }

        try {
            $status = $this->call('Status');
        } catch (RuntimeException $exception) {
            if (! str_contains($exception->getMessage(), "Function 'RandevuNotifications.Status' not found")) {
                throw $exception;
            }

            Log::warning('Native notification support is not registered in the connected Android runtime.', [
                'function' => 'RandevuNotifications.Status',
            ]);

            return [
                'available' => false,
                'notifications_granted' => false,
                'exact_alarms_granted' => false,
                'timezone' => null,
            ];
        }

        return [
            'available' => true,
            'notifications_granted' => (bool) ($status['notificationsGranted'] ?? false),
            'exact_alarms_granted' => (bool) ($status['exactAlarmsGranted'] ?? false),
            'timezone' => is_string($status['timeZoneId'] ?? null) ? $status['timeZoneId'] : null,
        ];
    }

    /** @param list<array<string,mixed>> $schedules */
    public function sync(array $schedules): void
    {
        $this->call('Sync', [
            'payload' => json_encode($schedules, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function requestPermission(): void
    {
        $this->call('RequestPermission');
    }

    public function openExactAlarmSettings(): void
    {
        $this->call('OpenExactAlarmSettings');
    }

    public function available(): bool
    {
        return ! app()->runningInConsole()
            && function_exists('nativephp_call')
            && Platform::isAndroid();
    }

    /** @param array<string,mixed> $parameters
     * @return array<string,mixed>
     */
    private function call(string $function, array $parameters = []): array
    {
        if (! $this->available()) {
            throw new RuntimeException('Local notifications are only available in the Android app.');
        }

        $payload = json_encode($parameters, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $response = nativephp_call('RandevuNotifications.'.$function, $payload);

        if (! is_string($response) || $response === '') {
            throw new RuntimeException("Native notification call [{$function}] returned no response.");
        }

        $decoded = json_decode($response, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException("Native notification call [{$function}] returned an invalid response.");
        }

        if (($decoded['status'] ?? null) === 'error') {
            throw new RuntimeException((string) ($decoded['message'] ?? 'Native notification call failed.'));
        }

        $data = $decoded['data'] ?? $decoded;

        return is_array($data) ? $data : [];
    }
}
