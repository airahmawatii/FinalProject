<?php
session_start();
require_once "../../../../app/config/database.php";

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db = new Database();
$pdo = $db->connect();

// Fetch all users with relevant details
$sql = "
    SELECT u.id, u.nama, u.email, u.role, u.status, u.created_at,
           m.nim, 
           d.nidn, d.nip,
           a.tahun as angkatan
    FROM users u
    LEFT JOIN mahasiswa m ON u.id = m.user_id
    LEFT JOIN dosen d ON u.id = d.user_id
    LEFT JOIN angkatan a ON m.angkatan_id = a.id_angkatan
    ORDER BY u.created_at DESC
";
$users = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=users_export_' . date('Y-m-d_H-i-s') . '.csv');

// Create file pointer
$output = fopen('php://output', 'w');

// Set CSV header row
fputcsv($output, ['ID', 'Nama', 'Email', 'Role', 'Status', 'NIM/NIDN/NIP', 'Angkatan', 'Tanggal Dibuat']);

// Loop through users and write to CSV
foreach ($users as $user) {
    $identifier = '';
    if ($user['role'] === 'mahasiswa') {
        $identifier = $user['nim'];
    } elseif ($user['role'] === 'dosen') {
        $identifier = $user['nidn'] ?: $user['nip'];
    }

    fputcsv($output, [
        $user['id'],
        $user['nama'],
        $user['email'],
        $user['role'],
        $user['status'],
        $identifier,
        $user['angkatan'] ?: '-',
        $user['created_at']
    ]);
}

fclose($output);
exit;
