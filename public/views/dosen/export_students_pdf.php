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
if (!$course_id) die("Course ID Not Found");

// Verify Owner
$courses = $courseModel->getByDosen($_SESSION['user']['id']);
$is_owner = false;
$courseData = null;
foreach($courses as $c) {
    if($c['id'] == $course_id) {
        $is_owner = true;
        $courseData = $c;
        break;
    }
}
if (!$is_owner) die("Access Denied");

$students = $enrollModel->getStudentsByCourse($course_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rekap Mahasiswa</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        
        table { w-full; width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; text-transform: uppercase; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .meta { margin-bottom: 20px; font-size: 14px; }
        .meta tr td { border: none; padding: 2px 10px 2px 0; }
    </style>
</head>
<body>

    <div id="content">
        <div class="header">
            <h1>Daftar Mahasiswa</h1>
            <p>TaskAcademia System</p>
        </div>

        <table class="meta">
            <tr>
                <td style="width: 120px; font-weight: bold;">Mata Kuliah</td>
                <td>: <?= htmlspecialchars($courseData['name']) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Semester</td>
                <td>: <?= htmlspecialchars($courseData['semester']) ?></td>
            </tr>
             <tr>
                <td style="font-weight: bold;">Dosen Pengampu</td>
                <td>: <?= htmlspecialchars($_SESSION['user']['nama']) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Total Mahasiswa</td>
                <td>: <?= count($students) ?></td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 40%;">Nama Lengkap</th>
                    <th style="width: 40%;">Email Kampus</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($students as $s): ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td style="text-align: center;">Aktif</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; font-size: 10px; color: #888; text-align: center;">
            Dicetak otomatis oleh sistem pada <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>

    <script>
        window.onload = function() {
            const element = document.getElementById('content');
            const filename = 'Absensi_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $courseData['name']) ?>.pdf';
            const opt = {
                margin:       10,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        };
    </script>

</body>
</html>
