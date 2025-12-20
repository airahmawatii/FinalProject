<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// Strict Status Check for Google Login Users
if ($_SESSION['user']['status'] !== 'active') {
    header("Location: " . BASE_URL . "/views/auth/pending.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';
require_once __DIR__ . '/../../../app/Models/CourseModel.php';
require_once __DIR__ . '/../../../app/Services/AnalyticsService.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);
$courseModel = new CourseModel($pdo);
$analytics = new AnalyticsService();

$dosen_id = $_SESSION['user']['id'];
$tasks = $taskModel->getByDosen($dosen_id);
$courses = $courseModel->getByDosen($dosen_id);

// Fetch Analytics Data
$workload = $analytics->getWorkloadStats($dosen_id);
$monthly = $analytics->getTasksPerMonth($dosen_id);

// Prepare Chart Data
$courseNames = array_column($workload, 'course_name');
$taskCounts = array_column($workload, 'task_count');
$months = array_column($monthly, 'month');
$monthlyCounts = array_column($monthly, 'count');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .sidebar {
            background: rgba(15, 23, 42, 0.95); /* Slate 900 */
        }
    </style>
    <?php include __DIR__ . '/../layouts/calendar_style.php'; ?>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex text-gray-800">

    <!-- Sidebar Integrated -->
    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full transition-all duration-300 md:ml-72">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Selamat Datang, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'])[0]) ?>! 👋</h1>
                    <p class="text-blue-200">Kelola aktivitas akademik Anda dengan mudah.</p>
                </div>
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <!-- Profile Widget -->
                    <div class="glass pl-2 pr-5 py-2 rounded-full flex items-center gap-3 cursor-pointer hover:scale-[1.02] transition shadow-xl border-none" onclick="window.location.href='profile.php'">
                        <?php 
                        $photo = $_SESSION['user']['photo'] ?? null;
                        $avatar = $photo ? BASE_URL . "/uploads/profiles/$photo" : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['user']['nama']) . "&background=random";
                        ?>
                        <img src="<?= $avatar ?>" class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm">
                        <div class="hidden md:block text-left">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Dosen</p>
                            <p class="text-sm font-bold text-gray-800 leading-none"><?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'])[0]) ?></p>
                        </div>
                    </div>
                </div>
            </header>

             <!-- Stats Grid (White Cards) -->
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="glass p-6 rounded-2xl relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-100/50 rounded-full group-hover:scale-150 transition duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-100 rounded-xl text-blue-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <span class="text-4xl font-extrabold text-blue-900"><?= count($courses) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700">Mata Kuliah</h3>
                        <p class="text-sm text-gray-500">Total kelas aktif semester ini</p>
                    </div>
                </div>
                
                <div class="glass p-6 rounded-2xl relative overflow-hidden group">
                     <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-100/50 rounded-full group-hover:scale-150 transition duration-500"></div>
                     <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-purple-100 rounded-xl text-purple-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <span class="text-4xl font-extrabold text-purple-900"><?= count($tasks) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700">Total Tugas</h3>
                        <p class="text-sm text-gray-500">Tugas yang telah dibuat</p>
                    </div>
                </div>

                <div class="glass p-6 rounded-2xl relative overflow-hidden group">
                     <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-100/50 rounded-full group-hover:scale-150 transition duration-500"></div>
                     <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-orange-100 rounded-xl text-orange-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-4xl font-extrabold text-orange-900"><?= count(array_filter($tasks, fn($t) => strtotime($t['deadline']) > time())) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700">Deadline Aktif</h3>
                        <p class="text-sm text-gray-500">Tugas yang belum jatuh tempo</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Stack -->
            <div class="space-y-10">
                
                <!-- Recent Tasks List (Full Width) -->
                <div class="glass rounded-3xl p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <span>📝</span> Tugas Terbaru
                        </h3>
                        <a href="buat_tugas.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition text-sm flex items-center gap-2">
                            <span>+</span> Buat
                        </a>
                    </div>
                    
                    <?php if (empty($tasks)): ?>
                        <div class="text-center py-8 text-gray-400">
                            <p>Belum ada data tugas.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid md:grid-cols-2 gap-4">
                            <?php foreach (array_slice($tasks, 0, 4) as $t): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-xl hover:bg-white hover:shadow-md transition group">
                                    <div>
                                        <h4 class="font-bold text-gray-800 group-hover:text-blue-600 transition break-words"><?= htmlspecialchars($t['task_title']) ?></h4>
                                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide"><?= htmlspecialchars($t['course_name']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-red-500 bg-red-50 px-3 py-1 rounded-full mb-1">
                                            <?= date('d M, H:i', strtotime($t['deadline'])) ?>
                                        </div>
                                        <a href="edit_tugas.php?id=<?= $t['id'] ?>" class="text-xs text-blue-500 hover:text-blue-700 font-bold">Edit</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Visualizations Grid (Analytics & Calendar) -->
                <!-- Visualizations Grid (Calendar Top, Analytics Bottom) -->
                <div class="space-y-8 w-full">
                    
                    <!-- Calendar Container (Big & Top) -->
                     <div class="glass rounded-3xl p-6 shadow-xl">
                        <h3 class="text-xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                            <span>📅</span> Jadwal Akademik
                        </h3>
                        <div id='calendar' class="text-sm"></div>
                     </div>

                    <!-- Analytics Container (Smaller & Bottom) -->
                     <div class="glass rounded-3xl p-6 shadow-xl">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-800">
                            <span>📊</span> Analitik Pembelajaran
                        </h3>
                        <!-- Grid for Side-by-Side Charts -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                            <!-- Workload Chart -->
                            <div>
                                <p class="text-sm font-bold text-gray-500 mb-4 uppercase text-center tracking-wide">Distribusi Tugas per Matkul</p>
                                <div class="h-64 flex justify-center relative">
                                    <canvas id="workloadChart"></canvas>
                                </div>
                            </div>
                            <!-- Trend Chart -->
                            <div class="border-t md:border-t-0 md:border-l border-gray-100 pt-8 md:pt-0 md:pl-8">
                                <p class="text-sm font-bold text-gray-500 mb-4 uppercase text-center tracking-wide">Aktivitas Bulanan</p>
                                <div class="h-64 relative w-full">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'title', right: 'prev,next' },
                titleFormat: { year: '2-digit', month: 'short' },
                height: 'auto',
                events: '<?= BASE_URL ?>/api/get_tasks.php',
                eventColor: '#2563EB'
            });
            calendar.render();

            // Charts Config ... (Simplified for brevity but functional)
            // Charts Config
            const commonOptions = {
                plugins: { legend: { display: false } }, 
                layout: { padding: 10 },
                responsive: true,
                maintainAspectRatio: false, // CRITICAL: Makes chart fill the h-64 container strictly
                scales: { 
                    x: { 
                        ticks: { color: '#64748b' }, 
                        grid: { display: false }, 
                        offset: true, 
                        border: { display: false }
                    },
                    y: { 
                        ticks: { color: '#64748b', stepSize: 1 }, 
                        grid: { color: '#f1f5f9', borderDash: [2, 2] },
                        beginAtZero: true
                    }
                }
            };

            // Workload (Doughnut) Options
            const doughnutOptions = {
                responsive: true,
                maintainAspectRatio: false, // CRITICAL
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } }, // Add Legend back for clarity if space permits
                layout: { padding: 20 },
                cutout: '70%'
            };

            new Chart(document.getElementById('workloadChart'), {
                type: 'doughnut', 
                data: {
                    labels: <?= json_encode($courseNames) ?>,
                    datasets: [{
                        data: <?= json_encode($taskCounts) ?>,
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#f97316', '#14b8a6'],
                        borderWidth: 0
                    }]
                },
                options: doughnutOptions
            });

            new Chart(document.getElementById('trendChart'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [{
                        data: <?= json_encode($monthlyCounts) ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: commonOptions
            });

             // Mobile Sidebar Toggle
            // Mobile Sidebar Logic REMOVED (Handled by sidebar_dosen.php)
        });
    </script>
</body>
</html>
