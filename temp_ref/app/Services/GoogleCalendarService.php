<?php

require_once __DIR__ . '/GoogleClientService.php';

use Google\Service\Calendar;
use Google\Service\Calendar\Event;

class GoogleCalendarService extends GoogleClientService
{
    private $service;
    private $pdo;
    private $userId;

    public function __construct($accessToken, $refreshToken = null, $userId = null, $pdo = null)
    {
        parent::__construct();
        $this->client->setAccessToken($accessToken);
        $this->pdo = $pdo;
        $this->userId = $userId;

        // Auto-Refresh Logic
        if ($this->client->isAccessTokenExpired()) {
            if ($refreshToken) {
                // Refresh the token
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                
                if (!isset($newToken['error'])) {
                    // Update Client
                    $this->client->setAccessToken($newToken);
                    
                    // Update Database (Keep it alive)
                    if ($this->pdo && $this->userId) {
                        $this->updateUserToken($newToken);
                    }
                } else {
                    throw new Exception("Gagal memperbarui izin Google. Silakan login ulang via Google.");
                }
            } else {
                 throw new Exception("Token Google kadaluarsa dan tidak ada Refresh Token. Mohon login ulang via Google.");
            }
        }

        $this->service = new Calendar($this->client);
    }

    private function updateUserToken($tokenData) {
        // Save new Access Token & Expiry
        $query = "UPDATE users SET access_token = ?, token_expires = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $expiresAt = time() + ($tokenData['expires_in'] ?? 3599);
        $stmt->execute([$tokenData['access_token'], $expiresAt, $this->userId]);
        
        // Update Session if matches current user
        if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $this->userId) {
            $_SESSION['user']['access_token'] = $tokenData['access_token'];
        }
    }

    public function addTaskEvent($taskTitle, $courseName, $description, $deadline)
    {
        // Define Start Time (Deadline - 1 hour, or just same as deadline since it's a deadline)
        // Let's make it a 1-hour event ending at the deadline
        $endDateTime = new DateTime($deadline);
        $startDateTime = clone $endDateTime;
        $startDateTime->modify('-1 hour');

        $event = new Event([
            'summary' => "[$courseName] $taskTitle",
            'description' => $description,
            'start' => [
                'dateTime' => $startDateTime->format(DateTime::RFC3339),
                'timeZone' => 'Asia/Jakarta',
            ],
            'end' => [
                'dateTime' => $endDateTime->format(DateTime::RFC3339),
                'timeZone' => 'Asia/Jakarta',
            ],
            'reminders' => [
                'useDefault' => FALSE,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 60],
                ],
            ],
        ]);

        $calendarId = 'primary';
        return $this->service->events->insert($calendarId, $event);
    }
}
