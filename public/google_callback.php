<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Smart Path Detection (Localhost vs Hosting)
$baseDir = __DIR__;
if (file_exists($baseDir . '/../app/config/config.php')) {
    // Localhost Structure (/public/google_callback.php)
    require_once $baseDir . '/../app/config/config.php';
    require_once $baseDir . '/../app/config/database.php';
    require_once $baseDir . '/../app/Services/GoogleClientService.php';
    require_once $baseDir . '/../app/Controllers/AuthController.php';
} elseif (file_exists($baseDir . '/app/config/config.php')) {
    // Hosting Structure (Root / google_callback.php)
    require_once $baseDir . '/app/config/config.php';
    require_once $baseDir . '/app/config/database.php';
    require_once $baseDir . '/app/Services/GoogleClientService.php';
    require_once $baseDir . '/app/Controllers/AuthController.php';
} else {
    die("Error: Configuration files not found. Check directory structure.");
}

// Hybrid Callback Handler
// 1. If NOT logged in -> It's a Login/Register attempt -> Delegate to AuthController
// 2. If logged in -> It's a Connect/Bind attempt -> Handle here

if (!isset($_SESSION['user'])) {
    // LOGIN FLOW
    $db = new Database();
    $conn = $db->connect();
    $auth = new AuthController($conn);
    $auth->googleCallback(); // This will eventually redirect to dashboard
    exit;
}

// BINDING FLOW (Untuk Hubungkan Kalender saat sudah Login)
if (isset($_GET['code'])) {
    $service = new GoogleClientService(true); // Force OAuth
    $client = $service->getClient();

    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception("Error fetching token: " . $token['error']);
        }

        $accessToken = $token['access_token'];
        $refreshToken = $token['refresh_token'] ?? null; 
        $expiresAt = time() + $token['expires_in'];

        $db = new Database();
        $pdo = $db->connect();
        $userModel = new UserModel($pdo);
        
        // Simpan ke kolom gcal_
        $userModel->updateGcalTokens($_SESSION['user']['id'], $accessToken, $refreshToken, $expiresAt);
        
        // Update Session agar dashboard tau kalau sudah konek
        $_SESSION['user']['gcal_connected'] = true;
        
        // Redirect balik ke dashboard sesuai Role
        $role = $_SESSION['user']['role'];
        $redirectUrl = ($role === 'dosen') ? BASE_URL . '/views/dosen/dashboard.php' : BASE_URL . '/views/mahasiswa/dashboard_mahasiswa.php';
        
        header("Location: $redirectUrl?msg=" . urlencode("Google Calendar Berhasil Terhubung! 📅")); 
        exit;

    } catch (Exception $e) {
        die("Error Binding Kalender: " . $e->getMessage());
    }
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}
