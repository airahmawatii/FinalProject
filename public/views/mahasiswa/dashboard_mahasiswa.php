<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
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

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);
$tasks = $taskModel->getByStudent($_SESSION['user']['id']);

// Get enrolled courses for semester info (Simplified)
$stmt = $pdo->prepare("SELECT DISTINCT c.semester FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.student_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$semesters = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Analytics Calculation
$now = time();
$nextDeadline = null;
$urgentCount = 0;

foreach ($tasks as $t) {
    $deadlineTime = strtotime($t['deadline']);
    if ($deadlineTime > $now) {
        if ($nextDeadline === null || $deadlineTime < strtotime($nextDeadline['deadline'])) {
            $nextDeadline = $t;
        }
        if ($deadlineTime - $now < 3 * 86400) $urgentCount++;
    }
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
        .sidebar { background: rgba(15, 23, 42, 0.95); } /* Slate 900 */
    </style>
    <?php include __DIR__ . '/../layouts/calendar_style.php'; ?>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex text-gray-800">

    <!-- Sidebar Integrated -->
    <?php include __DIR__ . '/../layouts/sidebar_mahasiswa.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full transition-all duration-300 md:ml-72">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[10%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[10%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Halo, <?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'] ?? $_SESSION['user']['email'])[0]) ?>! ✨</h1>
                    <p class="text-blue-200">Tetap semangat, pantau tugasmu hari ini.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="glass px-4 py-2 rounded-full flex items-center gap-2 text-sm text-blue-900 font-bold bg-white">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Online
                    </div>
                </div>
            </header>

            <!-- Featured Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
                
                <!-- Next Deadline Card (Gradient for emphasis) -->
                <div class="glass p-8 rounded-3xl bg-gradient-to-br from-orange-500 to-red-600 text-white lg:col-span-2 relative overflow-hidden shadow-2xl transform hover:scale-[1.01] transition-all border-none">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center gap-2 mb-3 text-white/90">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-bold uppercase tracking-wider text-xs">PRIORITAS UTAMA</span>
                            </div>
                            <?php if ($nextDeadline): ?>
                                <h2 class="text-3xl md:text-4xl font-extrabold mb-2 leading-tight break-words"><?= htmlspecialchars($nextDeadline['task_title']) ?></h2>
                                <p class="text-lg text-white/90 font-medium break-words"><?= htmlspecialchars($nextDeadline['course_name']) ?></p>
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
                                <h2 class="text-3xl font-bold">Semua Aman! 🎉</h2>
                                <p class="text-blue-100 mt-2">Tidak ada deadline dalam waktu dekat. Istirahatlah.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Summary Card (White Glass) -->
                <div class="glass p-6 rounded-3xl flex flex-col justify-center">
                    <h3 class="text-xl font-bold mb-6 text-center text-gray-800">Ringkasan</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-4 bg-blue-50 rounded-2xl">
                            <span class="text-gray-600 font-medium">Total Tugas</span>
                            <span class="text-2xl font-bold text-blue-600"><?= count($tasks) ?></span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-red-50 rounded-2xl border border-red-100">
                            <span class="text-red-600 font-medium">Urgent (<3 Hari)</span>
                            <span class="text-2xl font-bold text-red-600"><?= $urgentCount ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>/download_report.php" class="block w-full text-center bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 rounded-xl transition shadow-lg">
                            📄 Download Transkrip Tugas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="space-y-10">
                <!-- Task List Area -->
                <div class="w-full space-y-8">
                    <!-- Task List Moved to daftar_tugas.php -->
                    
                </div>

                <!-- Visualization Section (Timeline & Calendar) -->
                <!-- Visualization Section (Calendar TOP, Gantt BOTTOM) -->
                <div class="space-y-8 w-full">
                    
                     <!-- Calendar (Big & Top) -->
                    <div class="glass rounded-3xl p-6 shadow-xl">
                        <h3 class="text-xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                            <span>📅</span> Kalender Akademik
                        </h3>
                        <div id='calendar' class="text-sm"></div>
                    </div>

                        <!-- Gantt Chart (Bottom & Compact) -->
                        <div class="glass rounded-3xl p-6 shadow-xl">
                             <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-800">
                                <span>⏳</span> Timeline Pengerjaan
                            </h3>
                            <!-- Dynamic Height handled by JS -->
                            <div id="gantt-chart"></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch Data
            fetch('<?= BASE_URL ?>/api/get_tasks.php')
                .then(response => response.json())
                .then(tasks => {
                    // Calendar
                    var calendarEl = document.getElementById('calendar');
                    if (calendarEl) {
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            headerToolbar: { left: 'title', right: 'prev,next' },
                            titleFormat: { year: '2-digit', month: 'short' },
                            height: 'auto',
                            events: tasks.map(t => ({
                                title: t.title,
                                start: t.start, 
                                end: t.end,     
                                backgroundColor: '#2563EB',
                                borderColor: '#2563EB',
                                allDay: true 
                            })),
                            eventClick: function(info) {
                                Swal.fire({
                                    title: info.event.title,
                                    text: 'Deadline: ' + new Date(info.event.end).toLocaleDateString(),
                                    icon: 'info'
                                });
                            }
                        });
                        calendar.render();
                    }

                    // Gantt (ApexCharts)
                     var options = {
                        series: [{
                            name: 'Jadwal Pengerjaan',
                            data: tasks.map(t => ({
                                x: t.course, 
                                y: t.gantt.y,
                                fillColor: t.gantt.fillColor
                            }))
                        }],
                        chart: {
                            // Validasi Data Length untuk tinggi dinamis
                            height: tasks.length > 0 ? Math.max(350, tasks.length * 65) : 350, 
                            type: 'rangeBar',
                            fontFamily: 'Outfit, sans-serif',
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                barHeight: '40%', // Thinner bars for elegance
                                borderRadius: 4,
                                rangeBarGroupRows: true
                            }
                        },
                        xaxis: { 
                            type: 'datetime',
                            position: 'top', 
                            labels: {
                                format: 'dd MMM',
                                style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#334155', fontSize: '12px', fontWeight: 600 },
                                maxWidth: 120
                            }
                        },
                        grid: { 
                            show: true,
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: true } },
                            padding: { right: 20, left: 10, top: 0, bottom: 0 }
                        },
                        dataLabels: { enabled: false },
                        tooltip: {
                            theme: 'light',
                            style: { fontSize: '12px' },
                            x: { format: 'dd MMM yyyy' }
                        }
                    };
                    
                    var chartEl = document.querySelector("#gantt-chart");
                    if (chartEl) {
                        var chart = new ApexCharts(chartEl, options);
                        chart.render();
                    }
                });

            // Flash Message
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


    </script>

</body>
</html>