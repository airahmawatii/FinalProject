<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);
$tasks = $taskModel->getByStudent($_SESSION['user']['id']);

// Ambil data semester dari mata kuliah yang diambil mahasiswa
$stmt = $pdo->prepare("SELECT DISTINCT c.semester FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.student_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$semesters = $stmt->fetchAll(PDO::FETCH_COLUMN);

// --- HITUNGAN ANALITIK SEDERHANA ---
// Menghitung:
// 1. Tugas Selesai (Completed Count)
// 2. Deadline Terdekat (Next Deadline) - Untuk ditampilkan di "Featured Card"
// 3. Status Urgent (H-3 Deadline)
$now = time();
$nextDeadline = null;
$urgentCount = 0;
$completedCount = 0;

foreach ($tasks as $t) {
    if ($t['is_completed']) {
        $completedCount++;
    } else {
        $deadlineTime = strtotime($t['deadline']);
        if ($deadlineTime > $now) {
            // Logic cari deadline paling dekat yang belum lewat
            if ($nextDeadline === null || $deadlineTime < strtotime($nextDeadline['deadline'])) {
                $nextDeadline = $t;
            }
            // Logic Urgent: Deadline kurang dari 3 hari (3 * 24 jam)
            if ($deadlineTime - $now < 3 * 86400) $urgentCount++;
        }
    }
}

// Siapkan Data Gantt Chart (Timeline Tugas)
$ganttData = [];
foreach ($tasks as $task) {
    // Asumsi tanggal mulai: Tanggal dibuat atau H-7 sebelum deadline
    $startDate = isset($task['created_at']) ? strtotime($task['created_at']) : strtotime('-7 days', strtotime($task['deadline']));
    $endDate = strtotime($task['deadline']);
    
    // Hanya tampilkan tugas yang valid dan belum terlalu lama berlalu
    if ($endDate >= strtotime('today')) {
        $ganttData[] = [
            'x' => htmlspecialchars($task['task_title']),
            'y' => [
                $startDate * 1000, 
                $endDate * 1000
            ],
            // Warna: Merah jika deadline < 2 hari, sisanya Indigo
            'fillColor' => strtotime($task['deadline']) < strtotime('+2 days') ? '#ef4444' : '#6366f1',
            'course' => htmlspecialchars($task['course_name'] ?? 'Umum')
        ];
    }
}
// Urutkan berdasarkan deadline terdekat
usort($ganttData, function($a, $b) {
    return $a['y'][1] - $b['y'][1];
});
$ganttData = array_slice($ganttData, 0, 10); // Batasi 10 tugas saja

$totalTasks = count($tasks);
$completionRate = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;

$user = $_SESSION['user'];
$photoUrl = !empty($user['photo']) ? BASE_URL . "/uploads/profiles/" . $user['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=2563eb&color=fff&bold=true";

// Cek Koneksi Google Calendar (OAuth atau Service Account)
require_once __DIR__ . '/../../../app/Services/GoogleClientService.php';
$clientService = new GoogleClientService();
if ($clientService->isServiceAccount()) {
    $gcal_connected = true; 
} else {
    $stmt = $pdo->prepare("SELECT gcal_access_token FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $gcal_connected = !empty($stmt->fetchColumn());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        #calendar, #gantt-chart { min-height: 350px; color: #1e293b; }
    </style>
    <?php include __DIR__ . '/../layouts/calendar_style.php'; ?>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex text-gray-800">

    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 p-2 bg-slate-800 rounded-lg text-white md:hidden shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>

    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-72 backdrop-blur-2xl bg-slate-900/80 border-r border-white/10 flex flex-col z-40 transform -translate-x-full md:translate-x-0 md:relative md:shadow-none transition-transform duration-300 ease-in-out shadow-2xl text-white">
        <div class="p-8 border-b border-white/10 flex justify-between items-center bg-gradient-to-r from-transparent via-white/5 to-transparent">
            <div>
                <h2 class="text-2xl font-bold flex items-center gap-3 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">
                    <span class="text-3xl">🎓</span> TaskAcademy
                </h2>
                <p class="text-slate-400 text-[10px] mt-2 font-bold tracking-[0.2em] uppercase">Student Dashboard</p>
            </div>
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 scrollbar-thin scrollbar-thumb-white/10">
            <!-- Status Card -->
            <div class="mb-6 bg-gradient-to-br from-blue-600/20 to-indigo-600/20 rounded-2xl p-4 border border-white/10 mx-2 shadow-inner">
                <p class="text-[9px] text-blue-300 font-extrabold uppercase tracking-widest mb-1">Status Akademik</p>
                <p class="text-sm text-white font-bold flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    Semester Aktif: <?= !empty($semesters) ? max($semesters) : '1' ?>
                </p>
            </div>

            <a href="dashboard_mahasiswa.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold transition transform md:hover:scale-[1.02] border border-white/10">
                <span class="text-lg transition">🏠</span> Dashboard
            </a>
            
            <p class="text-[10px] font-bold text-slate-500 px-4 mt-8 mb-3 uppercase tracking-widest">Utama</p>
            <a href="daftar_tugas.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white font-medium transition duration-300 border border-transparent hover:border-white/10 group">
                <span>📝</span> Daftar Tugas
            </a>
            
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="flex-1 relative overflow-y-auto w-full md:w-auto">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[10%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[10%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Halo, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'] ?? $_SESSION['user']['email'])[0]) ?></h1>
                     <p class="text-blue-200">Pantau tugas dan jadwal akademik Anda hari ini.</p>
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
                                <img src="<?= $photoUrl ?>" alt="Profile" class="w-full h-full rounded-full object-cover border-2 border-white/20">
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
                                <div class="border-t border-white/10 my-1"></div>
                                <a href="<?= BASE_URL ?>/logout.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/logout">
                                    <span class="text-lg group-hover/logout:rotate-12 transition-transform">🚪</span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Featured Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
                
                <!-- Next Deadline Card -->
                <div class="glass p-8 rounded-3xl bg-gradient-to-br from-orange-500 to-red-600 text-white lg:col-span-2 relative overflow-hidden shadow-2xl transform hover:scale-[1.01] transition-all border-none">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center gap-2 mb-3 text-white/90">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-bold uppercase tracking-wider text-xs">PRIORITAS UTAMA</span>
                            </div>
                            <?php if ($nextDeadline): ?>
                                <h2 class="text-3xl md:text-4xl font-extrabold mb-2 leading-tight"><?= htmlspecialchars($nextDeadline['task_title']) ?></h2>
                                <p class="text-lg text-white/90 font-medium"><?= htmlspecialchars($nextDeadline['course_name']) ?></p>
                                <div class="mt-8 flex flex-wrap items-center gap-4">
                                    <div class="bg-white/20 backdrop-blur-md px-5 py-3 rounded-xl border border-white/20">
                                        <span class="block text-xs uppercase opacity-80 font-bold">Tenggat</span>
                                        <span class="font-bold text-xl"><?= date('d M Y', strtotime($nextDeadline['deadline'])) ?></span>
                                    </div>
                                    <div class="bg-white/20 backdrop-blur-md px-5 py-3 rounded-xl border border-white/20">
                                        <span class="block text-xs uppercase opacity-80 font-bold">Pukul</span>
                                        <span class="font-bold text-xl"><?= date('H:i', strtotime($nextDeadline['deadline'])) ?> WIB</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <h2 class="text-3xl font-bold">Tidak ada deadline mendesak</h2>
                                <p class="text-blue-100 mt-2">Saat ini tidak ada tugas dengan tenggat waktu kurang dari 3 hari.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="glass p-6 rounded-3xl flex flex-col justify-center shadow-xl">
                    <h3 class="text-xl font-bold mb-6 text-center text-gray-800">Ringkasan</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-4 bg-blue-50 rounded-2xl">
                            <span class="text-gray-600 font-medium">Total Tugas</span>
                            <span class="text-2xl font-bold text-blue-600"><?= count($tasks) ?></span>
                        </div>
                        
                        <div class="p-4 bg-green-50 rounded-2xl border border-green-100">
                             <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600 font-medium text-sm">Selesai</span>
                                <span class="text-green-600 font-bold text-sm"><?= $completionRate ?>%</span>
                            </div>
                            <div class="w-full bg-green-200 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full transition-all duration-500" style="width: <?= $completionRate ?>%"></div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center p-4 bg-red-50 rounded-2xl border border-red-100">
                            <span class="text-red-600 font-medium">Urgent (<3 Hari)</span>
                            <span class="text-2xl font-bold text-red-600"><?= $urgentCount ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-8 mb-10">
                <!-- Kalender Akademik (Now Acting as Gantt Too) -->
                <div class="glass rounded-3xl p-8 shadow-xl">
                     <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            Kalender Tugas & Akademik
                        </h3>
                        <div class="flex gap-2">
                            <?php if (!$gcal_connected): ?>
                                <a href="<?= BASE_URL ?>/connect_google.php" 
                                   class="bg-white text-blue-900 border border-blue-200 hover:bg-blue-50 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition shadow-md">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/></svg>
                                    Connect G-Calendar
                                </a>
                            <?php else: ?>
                                <button onclick="syncCalendar()" id="btn-sync-gcal"
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-500/50 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition shadow-md">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Sync Calendar
                                </button>
                            <?php endif; ?>
                         </div>
                    </div>
                    <div id='calendar'></div>
                </div>
            </div>

        </div>
    </main>

    <script>
        function syncCalendar() {
            const btn = document.getElementById('btn-sync-gcal');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="animate-spin">⌛</span> Syncing...';
            btn.disabled = true;

            fetch('<?= BASE_URL ?>/api/sync_gcal_mahasiswa.php')
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
                                    window.location.href = '<?= BASE_URL ?>/connect_google.php';
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
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');

            function toggleSidebar() {
                const isClosed = sidebar.classList.contains('-translate-x-full');
                if (isClosed) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                }
            }
            if(mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
                overlay.addEventListener('click', toggleSidebar);
                closeSidebarBtn.addEventListener('click', toggleSidebar);
            }

            // --- Inisialisasi Kalender (Logika Timeline/Gantt Digabung) ---
            try {
                // Siapkan data event dari PHP langsung agar logika Warna & Durasi lebih mudah diatur
                const calendarEvents = <?= json_encode(array_map(function($t) {
                    // Logic Start Date: created_at atau H-7 deadline
                    $start = !empty($t['created_at']) ? $t['created_at'] : date('Y-m-d H:i:s', strtotime('-7 days', strtotime($t['deadline'])));
                    $end = $t['deadline'];
                    
                    // Logic Warna: Merah jika deadline dekat (< 2 hari), Biru jika aman
                    $isUrgent = (strtotime($t['deadline']) - time()) < (2 * 86400); 
                    // Jika sudah selesai (completed), mungkin bisa kasih warna Hijau? Tapi user minta Merah/Biru aja.
                    
                    return [
                        'title' => $t['course_name'] ?? 'Tugas Umum', // HANYA NAMA MATKUL
                        'start' => $start,
                        'end' => $end,
                        'backgroundColor' => $isUrgent ? '#ef4444' : '#3b82f6', // Red-500 or Blue-500
                        'borderColor' => $isUrgent ? '#ef4444' : '#3b82f6',
                        'extendedProps' => [
                            'task_title' => $t['task_title'], // Simpan judul asli untuk tooltip/popup
                            'deadline' => $t['deadline']
                        ]
                    ];
                }, $tasks)) ?>;

                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: { left: 'title', right: 'prev,next today' },
                    titleFormat: { year: 'numeric', month: 'long' },
                    height: 'auto',
                    events: calendarEvents,
                    eventDisplay: 'block', // Kotak memanjang (Gantt Style)
                    dayMaxEvents: false, // Tumpuk semua, jangan disingkat
                    eventTimeFormat: { // Sembunyikan jam di kotak event agar muat nama matkul
                        hour: 'numeric',
                        minute: '2-digit',
                        meridiem: 'short',
                        omitZeroMinute: true,
                        meridiem: false
                    },
                    displayEventTime: false, // Opsi alternatif: sembunyikan jam total agar bersih
                    eventClick: function(info) {
                         Swal.fire({
                            title: info.event.extendedProps.task_title, // Judul Tugas di Popup
                            html: `
                                <div class="text-left">
                                    <p class="mb-1"><strong>Matkul:</strong> ${info.event.title}</p>
                                    <p class="mb-1"><strong>Deadline:</strong> ${moment(info.event.extendedProps.deadline).format('DD MMM YYYY, HH:mm')}</p>
                                    <div class="mt-4 flex justify-end">
                                        <a href="daftar_tugas.php" class="text-blue-500 hover:underline">Lihat Detail</a>
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

            <?php if (isset($_SESSION['flash_message'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?= addslashes($_SESSION['flash_message']) ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>
        });

        function toggleTask(taskId) {
            fetch('<?= BASE_URL ?>/api/toggle_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_id: taskId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); 
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            });
        }

        async function shareTask(taskId) {
            const { value: email } = await Swal.fire({
                title: 'Kirim ke Teman 📧',
                input: 'email',
                inputLabel: 'Masukkan email teman kamu',
                inputPlaceholder: 'nama@email.com',
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                confirmButtonColor: '#2563EB',
                cancelButtonText: 'Batal'
            });

            if (email) {
                Swal.showLoading();
                fetch('<?= BASE_URL ?>/api/share_task.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ task_id: taskId, email: email })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Terkirim!', 'Deadline berhasil dikirim ke temanmu.', 'success');
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                });
            }
        }
    </script>
</body>
</html>