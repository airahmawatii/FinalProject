<?php
session_start();
require_once "../../../../app/config/database.php";

if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak.");

$db = new Database();
$pdo = $db->connect();

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id]);

header("Location: index.php");
exit;
