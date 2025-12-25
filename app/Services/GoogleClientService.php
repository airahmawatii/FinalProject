<?php

// Memuat autoload composer untuk mengaktifkan library Google API client dan PHP Dotenv
require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Client;

/**
 * Class GoogleClientService
 * 
 * Pusat pengaturan koneksi ke Google API. 
 * Kelas ini menangani dua metode keamanan (Hybrid):
 * 1. Service Account: Untuk tugas otomatis di belakang layar (Cron Job / Robot).
 * 2. OAuth 2.0: Untuk interaksi langsung user (Login & Hubungkan Kalender).
 */
class GoogleClientService
{
    // Objek utama Google Client
    protected $client;
    // Status apakah saat ini menggunakan akun robot (Service Account) atau tidak
    protected $isServiceAccount = false;

    /**
     * Mempersiapkan koneksi ke Google.
     * 
     * @param bool $forceOAuth Jika TRUE, sistem akan mengabaikan robot dan memaksa mode Login User.
     */
    public function __construct($forceOAuth = false)
    {
        // Pastikan variabel dari file .env dimuat agar kita bisa membaca API Keys
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        // Inisialisasi library Google Client
        $this->client = new Client();
        
        // Lokasi file kunci rahasia akun robot
        $saCredentialsPath = __DIR__ . '/../google/credentials.json';
        
        // --- LOGIKA PEMILIHAN KONEKSI (HYBRID) ---
        // Jika file robot ada DAN kita tidak sedang memaksa login user, gunakan jalur Robot (lebih stabil).
        if (file_exists($saCredentialsPath) && !$forceOAuth) {
            $this->client->setAuthConfig($saCredentialsPath);
            // Izin yang diminta: Akses penuh ke Kalender.
            $this->client->setScopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ]);
            $this->isServiceAccount = true;
        } else {
            // Jalur Fallback: Gunakan OAuth (Mode User Login).
            // Data diambil dari file .env (Client ID & Secret).
            $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
            $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
            
            // Mengatur alamat tujuan setelah user selesai memilih akun Google.
            $redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';
            if (empty($redirectUri)) {
                // Jika di .env kosong, otomatis arahkan ke folder google_callback.php
                $redirectUri = BASE_URL . '/google_callback.php';
            }
            $this->client->setRedirectUri($redirectUri);

            // Izin yang diminta ke user: Email, Profil, dan Akses Kalender.
            $this->client->setScopes([
                'email', 
                'profile',
                'https://www.googleapis.com/auth/calendar'
            ]);
            // 'offline' agar kita mendapatkan Refresh Token (kunci abadi).
            $this->client->setAccessType('offline');
            // 'consent' memaksa Google memunculkan layar persetujuan agar Refresh Token keluar.
            $this->client->setPrompt('consent');
        }
    }

    /**
     * Mengambil objek client yang sudah siap dipakai.
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Mengecek apakah saat ini sistem sedang bertindak sebagai Robot.
     */
    public function isServiceAccount() {
        return $this->isServiceAccount;
    }

    /**
     * Mengambil alamat email bot (robot) dari file credentials.json.
     */
    public function getServiceAccountEmail() {
        if ($this->isServiceAccount) {
            $saCredentialsPath = __DIR__ . '/../google/credentials.json';
            $json = file_get_contents($saCredentialsPath);
            $data = json_decode($json, true);
            return $data['client_email'] ?? null;
        }
        return null;
    }

    /**
     * FITUR CERDAS: Auto-Refresh Token.
     * Memastikan aplikasi punya "Kunci" yang selalu aktif. Jika kunci mati (kadaluarsa),
     * fungsi ini akan otomatis meminta kunci baru ke Google tanpa mengganggu user.
     * 
     * @param array $userTokens Data token dari database.
     * @param int $userId ID User untuk keperluan update database.
     * @param PDO $pdo Koneksi ke database.
     * @return bool TRUE jika berhasil/masih aktif, FALSE jika gagal total.
     */
    public function authorizeAndGetTokens($userTokens, $userId, $pdo) {
        // Akun robot tidak butuh proses refresh manual.
        if ($this->isServiceAccount) {
            return true; 
        }

        // Jika user memang belum pernah konek ke Google sama sekali.
        if (empty($userTokens['access_token'])) {
            return false;
        }

        // Pasang token yang ada ke client Google.
        $this->client->setAccessToken($userTokens['access_token']);

        // Cek apakah token sudah mati? (Biasanya mati setelah 1 jam).
        if ($this->client->isAccessTokenExpired()) {
            // Jika kita punya "Kunci Cadangan" (Refresh Token).
            if (!empty($userTokens['refresh_token'])) {
                try {
                    // Minta token baru ke server Google menggunakan kunci cadangan.
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($userTokens['refresh_token']);
                    
                    if (!isset($newToken['error'])) {
                        $this->client->setAccessToken($newToken);
                        
                        // Simpan Kunci Baru tersebut ke database agar bisa dipakai lagi nanti.
                        $expires = time() + ($newToken['expires_in'] ?? 3599);
                        $stmt = $pdo->prepare("UPDATE users SET gcal_access_token = ?, gcal_token_expires = ? WHERE id = ?");
                        $stmt->execute([$newToken['access_token'], $expires, $userId]);
                        
                        return true;
                    }
                } catch (Exception $e) {
                    // Log error jika terjadi kegagalan sistem.
                    return false;
                }
            }
            return false;
        }

        return true;
    }
}
