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
     * Create Event in User's Primary Calendar
     * $user: Array from DB (must contain id, refresh_token etc)
     * $eventData: ['summary', 'description', 'start', 'end'] (Dates in ISO format or Y-m-d H:i:s)
     */
    public function createEvent($user, $eventData)
    {
        // 1. Get Valid Token
        $accessToken = GoogleTokenService::refreshTokenIfNeeded($user);
        if (!$accessToken) {
            error_log("CalendarService: Failed to get access token for User ID " . $user['id']);
            return false;
        }

        // 2. Setup Client with Token
        $serviceInfo = new GoogleClientService();
        $client = $serviceInfo->getClient();
        $client->setAccessToken($accessToken);

        // 3. Setup Calendar Service
        $service = new Calendar($client);

        // 4. Prepare Event
        // Ensure format is ISO 8601 for Google
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
