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
    
    // Validate config before redirecting
    if (empty($_ENV['GOOGLE_CLIENT_ID']) || empty($_ENV['GOOGLE_CLIENT_SECRET'])) {
        throw new Exception("Google API Credentials (Client ID/Secret) belum diset di file .env");
    }
    
    // Redirect to Google's OAuth 2.0 server
    $authUrl = $client->createAuthUrl();
    header("Location: " . $authUrl);
    exit;
} catch (Exception $e) {
    echo "
    <div style='font-family: sans-serif; padding: 40px; text-align: center; background: #0f172a; min-h: 100vh; color: white;'>
        <div style='background: rgba(255,255,255,0.05); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); display: inline-block; max-width: 500px;'>
            <div style='font-size: 50px; margin-bottom: 20px;'>⚠️</div>
            <h2 style='margin-bottom: 10px;'>Konfigurasi Google Bermasalah</h2>
            <p style='color: #94a3b8; line-height: 1.6;'>{$e->getMessage()}</p>
            <div style='margin-top: 30px;'>
                <a href='dashboard.php' style='color: #4ade80; text-decoration: none; font-weight: bold;'>← Kembali ke Dashboard</a>
            </div>
        </div>
    </div>";
    exit;
}