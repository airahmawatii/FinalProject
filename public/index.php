<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';

$db = new Database();
$conn = $db->connect();
$auth = new AuthController($conn);

if (isset($_GET['action']) && $_GET['action'] === 'google_login') {
    $auth->googleLogin();
}

if (isset($_GET['code'])) {
    $auth->googleCallback();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'] ?? '';

    if ($action === 'register') {
        echo $auth->register($name, $email, $password);
    } elseif ($action === 'login') {
        echo $auth->login($email, $password);
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
