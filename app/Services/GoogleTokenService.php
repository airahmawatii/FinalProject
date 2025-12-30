<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/GoogleClientService.php';

class GoogleTokenService
{
    /**
     * Cek apakah token sudah kadaluarsa, dan refresh jika perlu.
     * Mengembalikan access_token yang valid atau null jika gagal.
     */
    public static function refreshTokenIfNeeded($user)
    {
        // 1. Jika kita tidak punya refresh token, kita tidak bisa berbuat apa-apa kalau access token mati
        if (empty($user['refresh_token'])) {
            // Coba kembalikan access_token jika masih ada, kalau tidak ya gagal
            if (!empty($user['access_token'])) {
                return $user['access_token']; 
            }
            return null;
        }

        // 2. Cek apakah access token saat ini masih valid (beri waktu jeda 60 detik)
        if (!empty($user['token_expires']) && time() < ($user['token_expires'] - 60)) {
            return $user['access_token'];
        }

        // 3. Lakukan Refresh
        $service = new GoogleClientService();
        $client = $service->getClient();

        $newToken = $client->fetchAccessTokenWithRefreshToken($user['refresh_token']);

        if (isset($newToken['error'])) {
            return null;
        }

        // 4. Simpan token baru ke DB
        $accessToken = $newToken['access_token'];
        $expiresIn = $newToken['expires_in']; // detik
        $expiresAt = time() + $expiresIn;

        $db = new Database();
        $pdo = $db->connect();
        
        $stmt = $pdo->prepare("UPDATE users SET access_token=?, token_expires=? WHERE id=?");
        $stmt->execute([$accessToken, $expiresAt, $user['id']]);

        return $accessToken;
    }
}
