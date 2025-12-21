<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/UserModel.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $pdo = $db->connect();
    $userModel = new UserModel($pdo);

    $id = $_POST['user_id'];
    $role = $_POST['role']; // 'dosen' or 'mahasiswa'

    if ($userModel->activateUser($id, $role)) {
        header("Location: dashboard_admin.php?msg=success");
    } else {
        header("Location: dashboard_admin.php?msg=error");
    }
}
