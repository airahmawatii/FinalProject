<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    die("Akses ditolak.");
}

require_once "../../../app/config/database.php";

$db = new Database();
$pdo = $db->connect();
$student_id = $_SESSION['user']['id'];

// Data Query
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

$studentName = $_SESSION['user']['nama'];
$date = date('d F Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rekap Tugas</title>
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
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .bg-green { background-color: #dcfce7; color: #166534; }
        .bg-red { background-color: #fee2e2; color: #991b1b; }
        
        .meta { margin-bottom: 20px; font-size: 14px; }
        .meta tr td { border: none; padding: 2px 10px 2px 0; }
    </style>
</head>
<body>

    <div id="content">
        <div class="header">
            <h1>Rekap Tugas Mahasiswa</h1>
            <p>TaskAcademia System</p>
        </div>

        <table class="meta">
            <tr>
                <td style="width: 100px; font-weight: bold;">Nama</td>
                <td>: <?= htmlspecialchars($studentName) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tanggal Cetak</td>
                <td>: <?= $date ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Total Tugas</td>
                <td>: <?= count($tasks) ?></td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Mata Kuliah</th>
                    <th style="width: 25%;">Judul Tugas</th>
                    <th style="width: 15%;">Dosen</th>
                    <th style="width: 20%;">Deadline</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach($tasks as $t): ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($t['course_name']) ?></td>
                    <td><?= htmlspecialchars($t['task_title']) ?></td>
                    <td><?= htmlspecialchars($t['dosen_name']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($t['deadline'])) ?></td>
                    <td style="text-align: center;">
                        <?php if($t['status_pengerjaan'] === 'Selesai'): ?>
                            <span class="badge bg-green">Selesai</span>
                        <?php else: ?>
                            <span class="badge bg-red">Belum</span>
                        <?php endif; ?>
                    </td>
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
            const opt = {
                margin:       10,
                filename:     'Rekap_Tugas_<?= str_replace(' ', '_', $studentName) ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save().then(function(){
                // Optional: window.close() after download, but mostly browsers keep it open
                // window.history.back(); // Or go back automatically
            });
        };
    </script>

</body>
</html>
