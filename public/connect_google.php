<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/config/config.php';
// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../app/Services/GoogleClientService.php';

$google = new GoogleClientService(true);
$client = $google->getClient();

// Redirect to Google's OAuth 2.0 Server
header("Location: " . $client->createAuthUrl());
exit;
