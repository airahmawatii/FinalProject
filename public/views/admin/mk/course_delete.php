<?php
/**
 * Script untuk Menghapus Mata Kuliah
 * 
 * File ini menangani penghapusan data mata kuliah dari sistem.
 * Dilengkapi dengan pembersihan relasi di tabel dosen_courses agar tidak terjadi error database.
 */

session_start();

// 1. Keamanan: Hanya Admin yang boleh menghapus mata kuliah
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/CourseModel.php";

$db = new Database();
$pdo = $db->connect();
// Memanggil Model agar bisa digunakan (Ini yang tadi bikin Error 500 karena lupa dipanggil)
$courseModel = new CourseModel($pdo);

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID Mata Kuliah tidak ditemukan.");
}

try {
    // 2. Bersihkan dulu relasi pengajar (dosen_courses)
    // MySQL MariaDB biasanya sangat ketat, relasi harus dihapus dulu sebelum data utama dihapus.
    $stmt = $pdo->prepare("DELETE FROM dosen_courses WHERE matkul_id = ?");
    $stmt->execute([$id]);

    // 3. Eksekusi penghapusan mata kuliah lewat Model
    $courseModel->delete($id);

    // 4. Kembali ke daftar mata kuliah dengan pesan sukses
    header("Location: index.php?msg=deleted");
    exit;

} catch (Exception $e) {
    // Tampilkan pesan jika gagal hapus karena ada keterikatan data lain (misal sudah ada tugas)
    die("Gagal menghapus mata kuliah: " . $e->getMessage());
}
