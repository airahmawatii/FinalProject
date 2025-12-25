<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Enable Error Reporting for Debugging (On Hosting)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die("Error: Tidak dapat terhubung ke database. Pastikan konfigurasi di file .env sudah benar.");
}

$auth = new AuthController($conn);

if (isset($_GET['action']) && $_GET['action'] === 'google_login') {
    $auth->googleLogin();
}

if (isset($_GET['code'])) {
    $auth->googleCallback();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        // AuthController::login() handles its own POST data
        $auth->login();
    } elseif ($action === 'register') {
        echo "Fitur registrasi manual saat ini dinonaktifkan. Silakan gunakan Google Login atau hubungi admin.";
    }
} else {
    // Basic Routing
    if (isset($_GET['page']) && $_GET['page'] === 'login') {
        require_once __DIR__ . '/views/auth/login_view.php';
    } else {
        // Default to Landing Page
        require_once __DIR__ . '/views/landing.php';
    }
}
?>
