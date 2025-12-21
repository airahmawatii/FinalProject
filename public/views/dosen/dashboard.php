<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

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
$recommendations = $analytics->getRecommendations($dosen_id);

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
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            background: rgba(15, 23, 42, 0.95); /* Slate 900 */
        }
    </style>
    <?php include __DIR__ . '/../layouts/calendar_style.php'; ?>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex text-white">

    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Success/Info Message Alert -->
    <?php if (isset($_GET['msg'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= htmlspecialchars($_GET['msg']) ?>',
                background: 'rgba(15, 23, 42, 0.9)',
                color: '#fff',
                confirmButtonColor: '#2563eb',
                backdrop: `rgba(15, 23, 42, 0.4) blur(4px)`,
                customClass: {
                    popup: 'glass border border-white/10 rounded-3xl'
                }
            });
        </script>
    <?php endif; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full md:w-auto min-h-screen md:ml-20">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Selamat Datang, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'])[0]) ?>! ✨</h1>
                     <p class="text-blue-200">Kelola aktivitas akademik dan pantau progres mahasiswa Anda.</p>
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
                                <a href="profile.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/profile">
                                    <span class="text-lg group-hover/profile:scale-110 transition-transform">👤</span>
                                    Profile
                                </a>
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

             <!-- Recommendations Section -->
             <div class="mb-10">
                <h3 class="text-xl font-bold mb-4 text-white flex items-center gap-2">
                    <span>💡</span> Rekomendasi & Insight
                </h3>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($recommendations as $rec): ?>
                        <div class="p-4 rounded-xl border-l-4 flex items-start gap-3 shadow-lg glass
                            <?= $rec['type'] == 'warning' ? 'border-yellow-400 bg-yellow-50/10 text-yellow-100' : 
                            ($rec['type'] == 'info' ? 'border-blue-400 bg-blue-50/10 text-blue-100' : 'border-green-400 bg-green-50/10 text-green-100') ?>">
                            <div class="mt-1">
                                <?php if($rec['type'] == 'warning'): ?>
                                    <span class="text-xl">⚠️</span>
                                <?php elseif($rec['type'] == 'info'): ?>
                                    <span class="text-xl">ℹ️</span>
                                <?php else: ?>
                                    <span class="text-xl">✅</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-white"><?= $rec['type'] == 'warning' ? 'Perhatian' : ($rec['type'] == 'info' ? 'Info' : 'Segalanya Bagus') ?></p>
                                <p class="text-sm opacity-90"><?= htmlspecialchars($rec['message']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

             <!-- Stats Grid (White Cards) -->
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="glass p-6 rounded-2xl relative overflow-hidden group border border-white/20 hover:bg-white/20 transition">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/20 rounded-full group-hover:scale-150 transition duration-500"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-500/30 rounded-xl text-blue-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <span class="text-4xl font-extrabold text-white"><?= count($courses) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-white">Mata Kuliah</h3>
                        <p class="text-sm text-slate-300">Total kelas aktif semester ini</p>
                    </div>
                </div>
                
                <div class="glass p-6 rounded-2xl relative overflow-hidden group border border-white/20 hover:bg-white/20 transition">
                     <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-500/20 rounded-full group-hover:scale-150 transition duration-500"></div>
                     <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-indigo-500/30 rounded-xl text-indigo-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <span class="text-4xl font-extrabold text-white"><?= count($tasks) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-white">Total Tugas</h3>
                        <p class="text-sm text-slate-300">Tugas yang telah dibuat</p>
                    </div>
                </div>

                <div class="glass p-6 rounded-2xl relative overflow-hidden group border border-white/20 hover:bg-white/20 transition">
                     <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-600/20 rounded-full group-hover:scale-150 transition duration-500"></div>
                     <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-500/30 rounded-xl text-blue-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-4xl font-extrabold text-white"><?= count(array_filter($tasks, fn($t) => strtotime($t['deadline']) > time())) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-white">Deadline Aktif</h3>
                        <p class="text-sm text-slate-300">Tugas yang belum jatuh tempo</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                <!-- Analytics Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Charts Container -->
                    <div class="glass rounded-3xl p-8 border border-white/20">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-white">
                            <span>📊</span> Analitik Pembelajaran
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <p class="text-sm font-bold text-slate-300 mb-2 uppercase text-center">Distribusi Tugas per Matkul</p>
                                <canvas id="workloadChart"></canvas>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-300 mb-2 uppercase text-center">Aktivitas Bulanan</p>
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Tasks List -->
                    <div class="glass rounded-3xl p-8 border border-white/20">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                <span>📝</span> Tugas Terbaru
                            </h3>
                            <div class="flex gap-2">
                                <a href="<?= BASE_URL ?>/download_report.php?format=pdf" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-red-500/30 transition text-sm flex items-center gap-2">
                                    <span>📄</span> PDF
                                </a>
                                <a href="buat_tugas.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition text-sm flex items-center gap-2 border border-white/10">
                                    <span>+</span> Buat Tugas
                                </a>
                            </div>
                        </div>
                        
                        <?php if (empty($tasks)): ?>
                            <div class="text-center py-8 text-slate-400">
                                <p>Belum ada data tugas.</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach (array_slice($tasks, 0, 4) as $t): ?>
                                    <div class="flex items-center justify-between p-4 bg-white/5 border border-white/10 rounded-xl hover:bg-white/10 hover:shadow-md transition group">
                                        <div>
                                            <h4 class="font-bold text-white group-hover:text-blue-400 transition"><?= htmlspecialchars($t['task_title']) ?></h4>
                                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide"><?= htmlspecialchars($t['course_name']) ?></p>
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
                </div>
                
                <!-- Calendar (Sidebar Right) -->
                <div>
                     <div class="glass rounded-3xl p-6 h-full border border-white/20">
                        <h3 class="text-lg font-bold mb-4 text-white flex items-center gap-2">
                            <span>📅</span> Jadwal
                        </h3>
                        <div id='calendar' class="text-xs"></div>
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
            const commonOptions = {
                plugins: { legend: { display: false } }, // Clean look
                scales: { 
                    x: { ticks: { color: '#64748b' }, grid: { display: false } },
                    y: { ticks: { color: '#64748b' }, grid: { color: '#f1f5f9' } }
                }
            };

            new Chart(document.getElementById('workloadChart'), {
                type: 'doughnut', // Changed to Doughnut for variety
                data: {
                    labels: <?= json_encode($courseNames) ?>,
                    datasets: [{
                        data: <?= json_encode($taskCounts) ?>,
                        backgroundColor: ['#3b82f6', '#8b5cf6', '#f97316', '#14b8a6'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, cutout: '70%' }
            });

            new Chart(document.getElementById('trendChart'), {
                type: 'bar', // Changed to Bar
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


        });
    </script>
</body>
</html>
