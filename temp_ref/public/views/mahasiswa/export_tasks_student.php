<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    die("Akses ditolak.");
}

require_once "../../../app/config/database.php";

$db = new Database();
$pdo = $db->connect();

$student_id = $_SESSION['user']['id'];

// Get all tasks for this student
// We need to join with courses and dosen to get readable names
$query = "
    SELECT t.*, c.name as course_name, u.nama as dosen_name,
    CASE WHEN tc.completed_at IS NOT NULL THEN 'Selesai' ELSE 'Belum Selesai' END as status_pengerjaan
    FROM tasks t
    JOIN courses c ON t.course_id = c.id
    JOIN enrollments e ON e.course_id = c.id
    JOIN users u ON t.dosen_id = u.id
    LEFT JOIN task_completions tc ON tc.task_id = t.id AND tc.user_id = ?
    WHERE e.student_id = ?
    ORDER BY t.deadline ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([$student_id, $student_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set header download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rekap_tugas_saya_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Header
fputcsv($output, ['No', 'Mata Kuliah', 'Judul Tugas', 'Dosen', 'Deadline', 'Status']);

// Data
$no = 1;
foreach ($tasks as $t) {
    fputcsv($output, [
        $no++,
        $t['course_name'],
        $t['title'],
        $t['dosen_name'],
        $t['deadline'],
        $t['status_pengerjaan']
    ]);
}

fclose($output);
exit;
