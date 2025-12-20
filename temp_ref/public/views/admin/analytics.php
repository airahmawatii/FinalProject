<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login_view.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/Services/AnalyticsService.php';

$analytics = new AnalyticsService();
$stats = $analytics->getDashboardStats();
$workload = $analytics->getWorkloadStats();
$monthly = $analytics->getTasksPerMonth();

// Prepare data for Chart.js
$courseNames = array_column($workload, 'course_name');
$taskCounts = array_column($workload, 'task_count');

$months = array_column($monthly, 'month');
$monthlyCounts = array_column($monthly, 'count');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analitik & Laporan - TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex min-h-screen">
    <!-- Sidebar ( Simplified for this view, usually included via PHP ) -->
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:block">
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <span class="text-xl font-bold text-indigo-600">TaskAcademia</span>
        </div>
        <nav class="p-4 space-y-1">
            <a href="dashboard_admin.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Dashboard</a>
            <a href="#" class="block px-4 py-2 font-medium text-indigo-600 bg-indigo-50 rounded-lg">Analitik</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Analitik & Laporan</h1>
                <p class="text-gray-500">Statistik beban kerja dan ekspor data.</p>
            </div>
            <a href="../../download_report.php" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Lihat & Cetak Laporan (PDF)
            </a>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Total Tugas</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_tasks'] ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Total Mata Kuliah</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_courses'] ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Mahasiswa Aktif</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['active_students'] ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <p class="text-sm font-medium text-gray-500">Total User</p>
                <p class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_users'] ?></p>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Bar Chart: Beban Kerja per MK -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Beban Tugas per Mata Kuliah</h3>
                <canvas id="workloadChart"></canvas>
            </div>

            <!-- Line Chart: Tren Tugas Bulanan -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Tugas Masuk (Bulanan)</h3>
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </main>
</div>

<script>
    // Workload Chart
    const ctx1 = document.getElementById('workloadChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode($courseNames) ?>,
            datasets: [{
                label: 'Jumlah Tugas',
                data: <?= json_encode($taskCounts) ?>,
                backgroundColor: 'rgba(79, 70, 229, 0.6)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Trend Chart
    const ctx2 = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'Tugas Baru',
                data: <?= json_encode($monthlyCounts) ?>,
                borderColor: 'rgba(16, 185, 129, 1)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

</body>
</html>
