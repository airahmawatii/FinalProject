<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../../../app/config/config.php";
require_once "../../../../app/config/database.php";

// Admin check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db = new Database();
$pdo = $db->connect();

// Get Users
$users = $pdo->query("SELECT id, nama, email, role, status, created_at FROM users ORDER BY role, nama ASC")->fetchAll(PDO::FETCH_ASSOC);

// Set Headers for Download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_His') . '.csv"');

// Open Output Stream
$output = fopen('php://output', 'w');

// Add CSV Header
fputcsv($output, ['ID', 'Nama Lengkap', 'Email', 'Role', 'Status', 'Terdaftar Sejak']);

// Add Data
foreach ($users as $user) {
    fputcsv($output, [
        $user['id'],
        $user['nama'],
        $user['email'],
        $user['role'],
        $user['status'],
        $user['created_at']
    ]);
}

fclose($output);
exit;
