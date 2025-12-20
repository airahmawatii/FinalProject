<?php
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'dosen'])) {
    die("Access Denied");
}

require_once __DIR__ . '/../app/Services/AnalyticsService.php';

$service = new AnalyticsService();

$dosen_id = null;
if ($_SESSION['user']['role'] === 'dosen') {
    $dosen_id = $_SESSION['user']['id'];
}

$tasks = $service->getTasksReportData($dosen_id);

// Determine Back URL based on Role
$back_url = '#';
if ($_SESSION['user']['role'] === 'admin') {
    $back_url = 'views/admin/analytics.php';
} elseif ($_SESSION['user']['role'] === 'dosen') {
    $back_url = 'views/dosen/dashboard.php';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tugas TaskAcademia</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        h1 { text-align: center; margin-bottom: 5px; }
        p.subtitle { text-align: center; color: #666; font-size: 14px; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 12px; }
        th { background-color: #f4f4f4; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak PDF / Print
        </button>
        <a href="<?= $back_url ?>" class="no-print" style="text-decoration: none; padding: 10px 20px; background: #ddd; color: #333; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; display: inline-block;">
             Kembali
        </a>
    </div>

    <h1>Laporan Tugas & Deadline</h1>
    <p class="subtitle">TaskAcademia System • Generated on <?= date('d M Y, H:i') ?></p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Tugas</th>
                <th>Mata Kuliah</th>
                <th>Dosen Pengampu</th>
                <th>Deadline</th>
                <th>Dibuat Pada</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $i => $task): ?>
            <tr>
                <td style="text-align: center;"><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($task['task_title']) ?></td>
                <td><?= htmlspecialchars($task['course_name']) ?></td>
                <td><?= htmlspecialchars($task['dosen_name']) ?></td>
                <td style="color: #d9534f; font-weight: bold;"><?= date('d M Y, H:i', strtotime($task['deadline'])) ?></td>
                <td><?= date('d/m/Y', strtotime($task['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Auto trigger print dialog on load
        window.onload = function() {
            // setTimeout(() => window.print(), 500); 
            // Optional: Auto print, but maybe user wants to see it first.
        }
    </script>
</body>
</html>
