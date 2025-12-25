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
    // 2. Bersihkan semua data yang terikat dengan Mata Kuliah ini
    // MySQL mewajibkan data anak dihapus dulu sebelum data induk dihapus.

    // A. Hapus semua Tugas (Tasks) di matkul ini 
    // (Ini otomatis akan menghapus data di task_completions karena ada ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE course_id = ?");
    $stmt->execute([$id]);

    // B. Hapus semua pendaftaran mahasiswa (Enrollments) di matkul ini
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?");
    $stmt->execute([$id]);

    // C. Hapus relasi dosen (Dosen Courses) 
    // (Sebenarnya sudah otomatis CASCADE di database, tapi kita hapus manual untuk keamanan)
    $stmt = $pdo->prepare("DELETE FROM dosen_courses WHERE matkul_id = ?");
    $stmt->execute([$id]);

    // 3. Terakhir, baru hapus data Mata Kuliah utamanya
    $courseModel->delete($id);

    // 4. Kembali ke daftar mata kuliah dengan pesan sukses
    header("Location: index.php?msg=deleted");
    exit;

} catch (Exception $e) {
    // Tampilkan pesan jika gagal hapus karena ada keterikatan data lain (misal sudah ada tugas)
    die("Gagal menghapus mata kuliah: " . $e->getMessage());
}
