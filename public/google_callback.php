<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Services/GoogleClientService.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';

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

// BINDING FLOW (Existing Logic)
if (isset($_GET['code'])) {
    $service = new GoogleClientService(true); // Force OAuth for Account Binding
    $client = $service->getClient();

    try {
        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Check for errors
        if (isset($token['error'])) {
            throw new Exception("Error fetching token: " . $token['error']);
        }

        $accessToken = $token['access_token'];
        $refreshToken = $token['refresh_token'] ?? null; 
        $expiresIn = $token['expires_in'];
        $created = $token['created'];
        $expiresAt = $created + $expiresIn;

        $db = new Database();
        $pdo = $db->connect();
        
        // Update User
        if ($refreshToken) {
            $stmt = $pdo->prepare("UPDATE users SET access_token=?, refresh_token=?, token_expires=? WHERE id=?");
            $stmt->execute([$accessToken, $refreshToken, $expiresAt, $_SESSION['user']['id']]);
            $_SESSION['user']['refresh_token'] = $refreshToken; 
        } else {
            $stmt = $pdo->prepare("UPDATE users SET access_token=?, token_expires=? WHERE id=?");
            $stmt->execute([$accessToken, $expiresAt, $_SESSION['user']['id']]);
        }
        
        $_SESSION['user']['access_token'] = $accessToken;
        
        // Success: Redirect based on Role
        $role = $_SESSION['user']['role'];
        $redirectUrl = ($role === 'dosen') ? BASE_URL . '/views/dosen/dashboard.php' : BASE_URL . '/views/mahasiswa/dashboard_mahasiswa.php';
        
        header("Location: $redirectUrl?msg=google_connected"); 
        exit;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}
