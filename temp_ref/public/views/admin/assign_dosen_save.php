<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../app/config/database.php";
$db = new Database();
$pdo = $db->connect();

$stmt = $pdo->prepare("INSERT INTO dosen_courses (dosen_id, course_id) VALUES (?, ?)");
$stmt->execute([$_POST['dosen_id'], $_POST['course_id']]);

header("Location: assign_dosen.php");
exit;
