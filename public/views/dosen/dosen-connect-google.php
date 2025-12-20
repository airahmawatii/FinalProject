<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    die("Akses ditolak");
}

require_once __DIR__ . '/../../../vendor/autoload.php'; // Load composer autoloader for Google Client
require_once __DIR__ . '/../../../app/Services/GoogleClientService.php';

// No namespace needed as the class is in global namespace

try {
    $google = new GoogleClientService();
    $client = $google->getClient();
    
    // Redirect to Google's OAuth 2.0 server
    header("Location: " . $client->createAuthUrl());
    exit;
} catch (Exception $e) {
    die("Error initializing Google Client: " . $e->getMessage());
}
