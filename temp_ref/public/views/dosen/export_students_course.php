<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    die("Akses ditolak.");
}

require_once "../../../app/config/database.php";
require_once "../../../app/Models/CourseModel.php";
require_once "../../../app/Models/EnrollmentModel.php";

$db = new Database();
$pdo = $db->connect();
$enrollModel = new EnrollmentModel($pdo);
$courseModel = new CourseModel($pdo);

$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    die("Course ID tidak ditemukan.");
}

// Security: Check if dosen owns this course
$courses = $courseModel->getByDosen($_SESSION['user']['id']);
$is_owner = false;
$course_name = "course";
foreach($courses as $c) { 
    if($c['id'] == $course_id) {
        $is_owner = true; 
        $course_name = preg_replace('/[^A-Za-z0-9]/', '_', $c['name']); // Sanitize for filename
        break;
    } 
}

if (!$is_owner) {
    die("Akses ditolak: Anda tidak mengajar mata kuliah ini.");
}

$students = $enrollModel->getStudentsByCourse($course_id);

// Set header download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rekap_mahasiswa_' . $course_name . '_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Header
fputcsv($output, ['No', 'Nama Mahasiswa', 'Email']);

// Data
$no = 1;
foreach ($students as $s) {
    fputcsv($output, [
        $no++,
        $s['name'],
        $s['email']
    ]);
}

fclose($output);
exit;
