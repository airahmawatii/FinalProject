<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

// Security: Prevent Pending Users from bypassing
if ($_SESSION['user']['status'] === 'pending') {
    header("Location: " . BASE_URL . "/views/auth/pending.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';
require_once __DIR__ . '/../../../app/Models/CourseModel.php';
require_once __DIR__ . '/../../../app/Services/AnalyticsService.php';
require_once __DIR__ . '/../../../app/Services/GoogleClientService.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);
$courseModel = new CourseModel($pdo);
$analytics = new AnalyticsService();
$clientService = new GoogleClientService();

$dosen_id = $_SESSION['user']['id'];
$tasks = $taskModel->getByDosen($dosen_id);
$courses = $courseModel->getByDosen($dosen_id);

// Ambil Data Statistik Analitik
$workload = $analytics->getWorkloadStats($dosen_id);
$monthly = $analytics->getTasksPerMonth($dosen_id);
$recommendations = $analytics->getRecommendations($dosen_id);

// Siapkan Data untuk Grafik
$courseNames = array_column($workload, 'course_name');
$taskCounts = array_column($workload, 'task_count');
$months = array_column($monthly, 'month');
$monthlyCounts = array_column($monthly, 'count');

// --- LOGIKA CEK STATUS GOOGLE CALENDAR ---
// Kita menggunakan "Hybrid Check":
// 1. Cek Service Account (Robot) dulu. Jika aktif, maka dianggap connected.
// 2. Jika Service Account mati, baru cek token OAuth user di database.
if ($clientService->isServiceAccount()) {
    $gcal_connected = true; // Service Account acts as "Connected"
} else {
    $stmt = $pdo->prepare("SELECT gcal_access_token FROM users WHERE id = ?");
    $stmt->execute([$dosen_id]);
    $gcal_connected = !empty($stmt->fetchColumn());
}

// --- DATA UNTUK GANTT CHART/KALENDER ---
// Mengubah data tugas menjadi format yang bisa dibaca Chart/FullCalendar.
// Logic:
// - Start Date: Diambil dari created_at (atau H-7 deadline jika null)
// - End Date: Deadline tugas
// - Warna: Merah jika deadline < 2 hari, Biru jika masih lama.
$ganttData = [];
foreach ($tasks as $task) {
    $startDate = isset($task['created_at']) ? strtotime($task['created_at']) : strtotime('-7 days', strtotime($task['deadline']));
    $endDate = strtotime($task['deadline']);
    
    // Hanya tampilkan tugas yang memiliki tanggal valid dan belum kedaluwarsa (opsional)
    if ($endDate >= strtotime('today')) {
        $ganttData[] = [
            'x' => htmlspecialchars($task['task_title']),
            'y' => [
                $startDate * 1000, // JavaScript timestamp in milliseconds
                $endDate * 1000
            ],
            // Warna: Merah jika deadline < 2 hari, sisanya Indigo (biar beda dikit dari kalender)
            'fillColor' => strtotime($task['deadline']) < strtotime('+2 days') ? '#ef4444' : '#6366f1',
            'course' => htmlspecialchars($task['course_name'])
        ];
    }
}
// Urutkan berdasarkan deadline terdekat
usort($ganttData, function($a, $b) {
    return $a['y'][1] - $b['y'][1];
});
// Batasi hanya menampilkan 10 tugas terdekat agar diagram tidak terlalu penuh
$ganttData = array_slice($ganttData, 0, 10);

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
    <style>
        /* Override Calendar Variables for Dark Theme */
        .fc {
            --fc-page-bg-color: transparent !important;
            --fc-neutral-bg-color: rgba(255,255,255,0.05) !important;
            --fc-list-event-hover-bg-color: rgba(255,255,255,0.1) !important;
            --fc-theme-standard-border-color: rgba(255,255,255,0.1) !important;
            --fc-today-bg-color: rgba(59, 130, 246, 0.2) !important;
        }

        /* Specific Elements Visibility Fix */
        .fc-toolbar-title { color: #ffffff !important; }
        .fc-col-header-cell-cushion { color: #cbd5e1 !important; text-decoration: none !important; } /* Hari */
        .fc-daygrid-day-number { color: #e2e8f0 !important; text-decoration: none !important; font-weight: bold; } /* Tanggal */
        
        /* Buttons */
        .fc-button-primary { 
            background-color: rgba(255,255,255,0.1) !important; 
            border-color: rgba(255,255,255,0.2) !important;
            color: #ffffff !important;
        }
        .fc-button-primary:hover {
            background-color: rgba(255,255,255,0.2) !important;
        }
        .fc-button-active {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
        }
        
        .fc-button-active {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
        }
        
        /* Fix Table Borders */
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: rgba(255,255,255,0.1) !important;
        }
        /* Soft Today Background - Subtle Blue transparent, not white */
        .fc-day-today { background: rgba(59, 130, 246, 0.1) !important; }
    </style>
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
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Selamat Datang, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'])[0]) ?></h1>
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
                    Rekomendasi & Insight
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
                                <p class="font-bold text-sm text-white"><?= $rec['type'] == 'warning' ? 'Perhatian' : ($rec['type'] == 'info' ? 'Info' : 'Status Bagus') ?></p>
                                <p class="text-sm opacity-90"><?= htmlspecialchars($rec['message']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
             </div>

             <!-- Calendar (Merged Logic) -->
             <div class="mb-10">
                 <div class="glass rounded-3xl p-6 border border-white/20">
                     <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                         <h3 class="text-lg font-bold text-white flex items-center gap-2">
                             Kalender Tugas & Akademik
                         </h3>
                         <div class="flex gap-2">
                            <?php if (!$gcal_connected): ?>
                                <a href="<?= BASE_URL ?>/auth/google_calendar_auth.php?action=connect" 
                                   class="bg-white text-blue-900 border border-white/20 hover:bg-blue-50 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition shadow-lg">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/></svg>
                                    Hubungkan Google Calendar
                                </a>
                            <?php else: ?>
                                <!-- Tombol Sinkronisasi Manual (Muncul jika Terhubung) -->
                                <button onclick="syncCalendar()" id="btn-sync-gcal"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-500/50 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition shadow-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Sinkronisasi Kalender
                                </button>
                            <?php endif; ?>
                         </div>
                     </div>
                     <div id='calendar' style="min-height: 600px;"></div>
                 </div>
             </div>

             <script>
             function syncCalendar() {
                const btn = document.getElementById('btn-sync-gcal');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="animate-spin">⌛</span> Syncing...';
                btn.disabled = true;

                fetch('<?= BASE_URL ?>/api/sync_gcal_dosen.php')
                    .then(r => r.json())
                    .then(data => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        if(data.success) {
                            Swal.fire('Sukses!', data.message, 'success');
                        } else {
                            if(data.code === 'AUTH_REQUIRED') {
                                Swal.fire({
                                    title: 'Akses Ditolak',
                                    text: 'Izin Google Calendar kadaluarsa.',
                                    icon: 'warning',
                                    confirmButtonText: 'Connect Ulang',
                                    showCancelButton: true
                                }).then((result) => {
                                    if(result.isConfirmed) {
                                        window.location.href = '<?= BASE_URL ?>/auth/google_calendar_auth.php?action=connect';
                                    }
                                });
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        }
                    })
                    .catch(doc => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    });
             }
             </script>

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



            <div class="grid grid-cols-1 gap-8 mb-10">
                <!-- Analytics Section -->
                <div class="space-y-8">
                    <!-- Charts Container -->
                    <div class="glass rounded-3xl p-8 border border-white/20">
                        <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-white">
                            Analitik Pembelajaran
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <p class="text-sm font-bold text-slate-300 mb-2 uppercase text-center">Distribusi Tugas per Matkul</p>
                                <div style="position: relative; height: 300px;">
                                    <canvas id="workloadChart"></canvas>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-300 mb-2 uppercase text-center">Aktivitas Bulanan</p>
                                <div style="position: relative; height: 300px;">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Tasks List -->
                    <div class="glass rounded-3xl p-8 border border-white/20">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                Tugas Terbaru
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
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let calendar;

            // --- Inisialisasi Kalender (Logika Timeline & Gantt Digabung) ---
            try {
                // Siapkan data event dari variabel PHP secara langsung
                // Kita atur warna dan durasi task di sini agar tampil sebagai kotak memanjang (Event Block)
                const calendarEvents = <?= json_encode(array_map(function($t) {
                    $start = !empty($t['created_at']) ? $t['created_at'] : date('Y-m-d H:i:s', strtotime('-7 days', strtotime($t['deadline'])));
                    $end = $t['deadline'];
                    $isUrgent = (strtotime($t['deadline']) - time()) < (2 * 86400); 
                    
                    return [
                        'title' => $t['course_name'] ?? 'Tugas', // HANYA NAMA MATKUL
                        'start' => $start,
                        'end' => $end,
                        'backgroundColor' => $isUrgent ? '#ef4444' : '#3b82f6', 
                        'borderColor' => $isUrgent ? '#ef4444' : '#3b82f6',
                        'extendedProps' => [
                            'task_title' => $t['task_title'],
                            'deadline' => $t['deadline']
                        ]
                    ];
                }, $tasks)) ?>;

                var calendarEl = document.getElementById('calendar');
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: { left: 'title', right: 'prev,next today' },
                    titleFormat: { year: 'numeric', month: 'long' },
                    height: 'auto',
                    events: calendarEvents,
                    eventDisplay: 'block', // Kotak memanjang
                    dayMaxEvents: false, // Tumpuk semua
                    eventTimeFormat: { 
                        hour: 'numeric', minute: '2-digit', meridiem: 'short', omitZeroMinute: true, meridiem: false 
                    },
                    displayEventTime: false, 
                    eventClick: function(info) {
                         Swal.fire({
                            title: info.event.extendedProps.task_title,
                            html: `
                                <div class="text-left">
                                    <p class="mb-1"><strong>Matkul:</strong> ${info.event.title}</p>
                                    <p class="mb-1"><strong>Deadline:</strong> ${moment(info.event.extendedProps.deadline).format('DD MMM YYYY, HH:mm')}</p>
                                    <div class="mt-4 flex justify-end">
                                        <a href="edit_tugas.php?id=${info.event.id}" class="text-blue-500 hover:underline">Lihat Detail</a>
                                    </div>
                                </div>
                            `,
                            icon: 'info',
                            confirmButtonColor: '#3b82f6'
                        });
                    }
                });
                calendar.render();
            } catch (e) {
                console.error("Calendar Error:", e);
                document.getElementById('calendar').innerHTML = '<p class="text-red-400">Gagal memuat kalender.</p>';
            }

            // --- 3. Inisialisasi Grafik Analitik ---
            try {
                const commonOptions = {
                    plugins: { legend: { display: false } }, // Sembunyikan legenda default
                    responsive: true,
                    maintainAspectRatio: false, // Agar grafik menyesuaikan tinggi container
                    scales: { 
                        x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
                        y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                    }
                };

                const workloadCtx = document.getElementById('workloadChart');
                if(workloadCtx) {
                    new Chart(workloadCtx, {
                        type: 'doughnut',
                        data: {
                            labels: <?= json_encode($courseNames) ?>,
                            datasets: [{
                                data: <?= json_encode($taskCounts) ?>,
                                backgroundColor: ['#3b82f6', '#8b5cf6', '#f97316', '#14b8a6', '#ef4444'],
                                borderWidth: 0
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: { legend: { position: 'bottom', labels: { color: '#cbd5e1' } } }
                        }
                    });
                }

                const trendCtx = document.getElementById('trendChart');
                if(trendCtx) {
                    new Chart(trendCtx, {
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
                }
            } catch (e) {
                console.error("Analytics Chart Error:", e);
            }

        });
    </script>
</body>
</html>
