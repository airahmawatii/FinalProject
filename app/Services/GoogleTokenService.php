<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/GoogleClientService.php';

class GoogleTokenService
{
    /**
     * Check if token is expired, and refresh if needed.
     * Returns valid access_token or null.
     */
    public static function refreshTokenIfNeeded($user)
    {
        // 1. If we don't have refresh token, we can't do anything if access token expires
        if (empty($user['refresh_token'])) {
            // Try to return access_token if it's still valid, otherwise fail
            if (!empty($user['access_token'])) {
                return $user['access_token']; 
            }
            return null;
        }

        // 2. Check if current access token is still valid (give 60s buffer)
        if (!empty($user['token_expires']) && time() < ($user['token_expires'] - 60)) {
            return $user['access_token'];
        }

        // 3. Refresh It
        $service = new GoogleClientService();
        $client = $service->getClient();

        $newToken = $client->fetchAccessTokenWithRefreshToken($user['refresh_token']);

        if (isset($newToken['error'])) {
            return null;
        }

        // 4. Save new token to DB
        $accessToken = $newToken['access_token'];
        $expiresIn = $newToken['expires_in']; // seconds
        $expiresAt = time() + $expiresIn;

        $db = new Database();
        $pdo = $db->connect();
        
        $stmt = $pdo->prepare("UPDATE users SET access_token=?, token_expires=? WHERE id=?");
        $stmt->execute([$accessToken, $expiresAt, $user['id']]);

        return $accessToken;
    }
}
