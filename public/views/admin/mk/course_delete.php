<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/CourseModel.php";

$db = new Database();
$pdo = $db->connect();

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak ditemukan");

// hapus relasi dosen-mk (multi dosen)
$stmt = $pdo->prepare("DELETE FROM dosen_courses WHERE matkul_id = ?");
$stmt->execute([$id]);

// hapus mata kuliah
$model->delete($id);

header("Location: index.php?msg=deleted");
exit;


