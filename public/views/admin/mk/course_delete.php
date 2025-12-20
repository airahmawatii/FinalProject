<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/CourseModel.php";

$db = new Database();
$pdo = $db->connect();

$model = new CourseModel($pdo);
$model->delete($_GET['id']);

header("Location: index.php?msg=deleted");
exit;
