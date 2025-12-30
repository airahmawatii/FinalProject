<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/GoogleTokenService.php';
require_once __DIR__ . '/GoogleClientService.php';

use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class CalendarService
{
    /**
     * Membuat Event Baru di Kalender Utama User
     *
     * @param array $user Data User dari Database (wajib berisi id, refresh_token, dll)
     * @param array $eventData Data Event: ['summary', 'description', 'start', 'end']
     *                         (Format tanggal harus ISO 8601 atau 'Y-m-d H:i:s')
     */
    public function createEvent($user, $eventData)
    {
        // 1. Dapatkan Token Valid (Refresh jika perlu)
        $accessToken = GoogleTokenService::refreshTokenIfNeeded($user);
        if (!$accessToken) {
            error_log("CalendarService: Gagal mendapatkan access token untuk User ID " . $user['id']);
            return false;
        }

        // 2. Siapkan Client Google dengan Token tadi
        $serviceInfo = new GoogleClientService();
        $client = $serviceInfo->getClient();
        $client->setAccessToken($accessToken);

        // 3. Siapkan Layanan Kalender
        $service = new Calendar($client);

        // 4. Siapkan Data Event
        // Pastikan format tanggal mengikuti standar ISO 8601 agar diterima Google
        $startStr = date('c', strtotime($eventData['start']));
        $endStr   = date('c', strtotime($eventData['end']));

        $event = new Event([
            'summary' => $eventData['summary'],
            'description' => $eventData['description'] ?? '',
            'start' => new EventDateTime([
                'dateTime' => $startStr,
                'timeZone' => 'Asia/Jakarta',
            ]),
            'end' => new EventDateTime([
                'dateTime' => $endStr,
                'timeZone' => 'Asia/Jakarta',
            ]),
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 60],
                ],
            ],
        ]);

        try {
            $calendarId = 'primary';
            $eventResult = $service->events->insert($calendarId, $event);
            return $eventResult->htmlLink;
        } catch (Exception $e) {
            error_log("CalendarService Error: " . $e->getMessage());
            return false;
        }
    }
}
