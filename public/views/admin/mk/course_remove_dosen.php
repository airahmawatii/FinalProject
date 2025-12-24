<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";

$matkul_id = $_GET['id'] ?? null;
$dosen_id = $_GET['dosen_id'] ?? null;

if (!$matkul_id || !$dosen_id) {
    header("Location: index.php");
    exit;
}

$db = new Database();
$pdo = $db->connect();

// Hapus relasi dosen-matkul
$del = $pdo->prepare("DELETE FROM dosen_courses WHERE dosen_id=? AND matkul_id=?");
$del->execute([$dosen_id, $matkul_id]);

header("Location: course_edit.php?id=$matkul_id&msg=removed");
exit;
