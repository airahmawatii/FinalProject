<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope('email');
$client->addScope('profile');
$client->addScope('https://www.googleapis.com/auth/calendar'); // Add Calendar

$client->setAccessType('offline'); // Crucial for Refresh Token
$client->setPrompt('consent');     // Force permission screen to get refresh_token every time
