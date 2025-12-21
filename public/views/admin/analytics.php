<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex font-outfit text-white">
    <?php include __DIR__ . '/../layouts/sidebar_admin.php'; ?>
    <main class="flex-1 min-h-screen relative">
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[100px]"></div>
        </div>

        <div class="p-6 md:p-10 max-w-7xl mx-auto pt-20 md:pt-10 relative z-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Analitik & Laporan</h1>
                    <p class="text-blue-200">Statistik beban kerja dan ekspor data sistem.</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Online Badge -->
                    <div class="glass px-4 py-2 rounded-full flex items-center gap-2 text-sm text-blue-900 font-bold bg-white/80 backdrop-blur-sm hidden md:flex">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Online
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative group">
                        <button class="glass pl-2 pr-4 py-1.5 rounded-full flex items-center gap-3 text-left hover:bg-white/20 transition shadow-lg border border-white/10 ring-2 ring-blue-500/20">
                            <div class="w-10 h-10 rounded-full p-[2px] bg-gradient-to-br from-blue-400 to-indigo-600 shadow-inner">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['nama']) ?>&background=2563eb&color=fff&bold=true" 
                                     alt="Profile" class="w-full h-full rounded-full object-cover border-2 border-white/20">
                            </div>
                            <div class="hidden md:block text-right">
                                <p class="text-sm font-bold text-white leading-none"><?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'])[0]) ?></p>
                                <p class="text-[10px] text-blue-200 uppercase font-semibold tracking-wider mt-0.5"><?= $_SESSION['user']['role'] ?></p>
                            </div>
                            <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 top-full mt-2 w-48 opacity-0 translate-y-2 pointer-events-none group-hover:opacity-100 group-hover:translate-y-0 group-hover:pointer-events-auto transition-all duration-300 z-50">
                            <div class="glass rounded-2xl p-2 shadow-2xl border border-white/20 overflow-hidden bg-slate-900/90 backdrop-blur-xl">
                                <a href="../../logout.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/logout">
                                    <span class="text-lg group-hover/logout:rotate-12 transition-transform">🚪</span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Action Bar -->
            <div class="flex justify-end mb-8">
                <a href="../../download_report.php" target="_blank" class="glass px-6 py-4 rounded-2xl font-bold text-white hover:bg-white/20 transition flex items-center gap-2 shadow-lg border border-white/10">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Ekspor Laporan PDF</span>
                </a>
            </div>

            <!-- Analytics Header -->
            <div class="mb-10">
                <h2 class="text-2xl font-bold flex items-center gap-3 text-white">
                    <span class="p-2 bg-blue-500/20 rounded-lg text-blue-400">📊</span>
                    Real-time Performance Analytics
                </h2>
                <p class="text-slate-400 mt-1">Pantau performa akademik dan beban kerja mahasiswa secara real-time.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div class="glass p-7 rounded-3xl border border-white/20 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-full -mr-8 -mt-8 blur-2xl group-hover:bg-blue-500/10 transition-all"></div>
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-blue-300">Total Users</span>
                    </div>
                    <h3 class="text-4xl font-bold text-white"><?= $stats['total_users'] ?></h3>
                </div>

                <div class="glass p-7 rounded-3xl border border-white/20 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/5 rounded-full -mr-8 -mt-8 blur-2xl group-hover:bg-purple-500/10 transition-all"></div>
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white shadow-lg shadow-purple-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-purple-300">Dosen</span>
                    </div>
                    <h3 class="text-4xl font-bold text-white"><?= $stats['total_dosen'] ?></h3>
                </div>

                <div class="glass p-7 rounded-3xl border border-white/20 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full -mr-8 -mt-8 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-emerald-300">Mahasiswa</span>
                    </div>
                    <h3 class="text-4xl font-bold text-white"><?= $stats['total_mahasiswa'] ?></h3>
                </div>

                <div class="glass p-7 rounded-3xl border border-white/20 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-orange-500/5 rounded-full -mr-8 -mt-8 blur-2xl group-hover:bg-orange-500/10 transition-all"></div>
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center text-white shadow-lg shadow-orange-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-orange-300">Active Tasks</span>
                    </div>
                    <h3 class="text-4xl font-bold text-white"><?= $stats['total_tasks'] ?></h3>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10">
                <!-- Bar Chart -->
                <div class="glass p-10 rounded-[2.5rem] border border-white/20 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Workload per Course</h3>
                    <p class="text-sm text-slate-400 mb-8">Jumlah tugas aktif berdasarkan mata kuliah saat ini.</p>
                    <div class="h-[350px] relative mt-4">
                        <canvas id="workloadChart"></canvas>
                    </div>
                </div>

                <!-- Line Chart -->
                <div class="glass p-10 rounded-[2.5rem] border border-white/20 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.001 8.001 0 0117.748 8H12V2.252z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Monthly Task Trends</h3>
                    <p class="text-sm text-slate-400 mb-8">Fluktuasi pembuatan tugas per bulan di tahun ini.</p>
                    <div class="h-[350px] relative mt-4">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chart defaults
        Chart.defaults.color = 'rgba(255, 255, 255, 0.5)';
        Chart.defaults.font.family = "'Outfit', sans-serif";
        Chart.defaults.font.size = 11;

        // Workload Chart
        const ctxWorkload = document.getElementById('workloadChart').getContext('2d');
        const workloadData = <?= json_encode($workload) ?>;
        const workloadGradient = ctxWorkload.createLinearGradient(0, 0, 0, 400);
        workloadGradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
        workloadGradient.addColorStop(1, 'rgba(37, 99, 235, 0.1)');

        new Chart(ctxWorkload, {
            type: 'bar',
            data: {
                labels: workloadData.map(d => d.course_name),
                datasets: [{
                    label: 'Active Tasks',
                    data: workloadData.map(d => d.task_count), // Changed to task_count to match PHP output
                    backgroundColor: workloadGradient,
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    borderRadius: 12,
                    borderSkipped: false,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Trend Chart
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        const trendData = <?= json_encode($monthly) ?>; // Corrected variable name from $monthlyTrends
        const trendGradient = ctxTrend.createLinearGradient(0, 0, 0, 400);
        trendGradient.addColorStop(0, 'rgba(139, 92, 246, 0.4)');
        trendGradient.addColorStop(1, 'rgba(139, 92, 246, 0)');

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendData.map(d => {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const monthIndex = parseInt(d.month.split('-')[1]) - 1; // Parse MM from "YYYY-MM"
                    return months[monthIndex];
                }),
                datasets: [{
                    label: 'New Tasks',
                    data: trendData.map(d => d.count), 
                    fill: true,
                    backgroundColor: trendGradient,
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 4,
                    pointBackgroundColor: 'rgba(139, 92, 246, 1)',
                    pointBorderColor: 'rgba(255, 255, 255, 0.5)',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: { 
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
