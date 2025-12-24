<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Client;

class GoogleClientService
{
    protected $client;
    protected $isServiceAccount = false;

    /**
     * Konstruktor Service Google Client (Hybrid)
     * 
     * @param bool $forceOAuth Jika true, maka akan memaksa penggunaan mode Login OAuth (untuk user login biasa).
     *                         Jika false (default), akan mencoba mencari Service Account (Robot) terlebih dahulu.
     */
    public function __construct($forceOAuth = false)
    {
        // Load .env if not already loaded
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        $this->client = new Client();
        
        // Cek apakah ada file Service Account Credentials (credentials.json)
        $saCredentialsPath = __DIR__ . '/../google/credentials.json';
        
        // --- LOGIC HYBRID AUTHENTICATION ---
        // 1. Service Account (Prioritas Utama untuk Background Job):
        //    Digunakan jika file credentials.json ADA dan kita TIDAK sedang melakukan login user ($forceOAuth = false).
        //    Ini memungkinkan "Robot" bekerja otomatis tanpa token user.
        //
        // 2. OAuth Client (Mode User Login):
        //    Digunakan jika tidak ada Service Account ATAU kita memang ingin user login ($forceOAuth = true).
        if (file_exists($saCredentialsPath) && !$forceOAuth) {
            $this->client->setAuthConfig($saCredentialsPath);
            $this->client->setScopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ]);
            $this->isServiceAccount = true;
        } else {
            // Fallback ke OAuth Client ID (Logic Lama / Untuk Login)
            $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
            $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
            $this->client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
            $this->client->setScopes([
                'email', 
                'profile',
                'https://www.googleapis.com/auth/calendar'
            ]);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function isServiceAccount() {
        return $this->isServiceAccount;
    }

    public function getServiceAccountEmail() {
        if ($this->isServiceAccount) {
            $saCredentialsPath = __DIR__ . '/../google/credentials.json';
            $json = file_get_contents($saCredentialsPath);
            $data = json_decode($json, true);
            return $data['client_email'] ?? null;
        }
        return null;
    }


}
