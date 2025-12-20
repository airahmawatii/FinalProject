<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Client;

class GoogleClientService
{
    protected $client;

    public function __construct()
    {
        // Load .env if not already loaded
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        // Use ENV vars directly
        $this->client = new Client();
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

    public function getClient()
    {
        return $this->client;
    }
}
