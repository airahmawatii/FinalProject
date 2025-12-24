<?php
require 'vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    echo "Error loading .env: " . $e->getMessage() . "\n";
    exit;
}

echo "--- ENV CONSTANTS ---\n";
echo "GOOGLE_CLIENT_ID: " . ($_ENV['GOOGLE_CLIENT_ID'] ? "Present" : "MISSING") . "\n";
echo "GOOGLE_CLIENT_SECRET: " . ($_ENV['GOOGLE_CLIENT_SECRET'] ? "Present" : "MISSING") . "\n";
echo "GOOGLE_REDIRECT_URI: '" . ($_ENV['GOOGLE_REDIRECT_URI'] ?? '') . "'\n";

echo "\n--- GENERATED AUTH URL ---\n";
try {
    $client = new Google\Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
    $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
    $client->setScopes(['email', 'profile']);
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    
    $url = $client->createAuthUrl();
    echo "URL: " . $url . "\n";
} catch (Exception $e) {
    echo "Error generating URL: " . $e->getMessage() . "\n";
}
