<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);
$tasks = $taskModel->getByStudent($_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .sidebar { background: rgba(15, 23, 42, 0.95); }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex text-white">

    <!-- Main Content (Full Page) -->
    <main class="w-full min-h-screen flex flex-col items-center justify-center p-4 md:p-8 relative">
        
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="w-full max-w-7xl relative z-10">
            <!-- Glass Container -->
            <div class="glass rounded-3xl p-8 md:p-10 shadow-2xl">
                
                <!-- Header with Back Button -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b border-gray-100 pb-6">
                    <div>
                         <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-400 hover:text-blue-600 mb-2 transition text-sm font-semibold group">
                            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Kembali
                        </a>
                        <h1 class="text-3xl font-bold text-gray-800">Daftar Tugas</h1>
                        <p class="text-gray-500 mt-1">Semua tugas yang perlu kamu selesaikan.</p>
                    </div>
                    <div class="bg-blue-100 text-blue-700 font-bold px-6 py-3 rounded-full text-sm shadow-sm">
                        <?= count($tasks) ?> Tugas
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <?php if (empty($tasks)): ?>
                        <div class="p-12 text-center rounded-3xl border border-dashed border-gray-300 bg-gray-50/50">
                            <div class="text-6xl mb-4">✨</div>
                            <h3 class="text-xl font-bold text-gray-700">Belum ada tugas</h3>
                            <p class="text-gray-500 mt-2">Nikmati waktu luangmu!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($tasks as $t): ?>
                            <?php $isDone = $t['is_completed']; ?>
                            <div class="bg-white/60 p-6 rounded-2xl flex flex-col md:flex-row justify-between items-start gap-6 hover:shadow-xl transition group border border-gray-100 hover:bg-white <?= $isDone ? 'opacity-75 grayscale-[30%]' : '' ?>">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider"><?= htmlspecialchars($t['course_name']) ?></span>
                                        <?php if($isDone): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                Selesai
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="text-xl font-bold mb-2 text-gray-800 group-hover:text-blue-600 transition <?= $isDone ? 'line-through text-gray-400' : '' ?>"><?= htmlspecialchars($t['task_title']) ?></h3>
                                    <p class="text-xs text-gray-400 font-semibold uppercase mb-3">Dosen: <?= htmlspecialchars($t['dosen_name']) ?></p>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?= htmlspecialchars($t['description']) ?></p>
                                    <div class="flex items-center text-red-500 font-bold text-sm bg-red-50 inline-block px-3 py-1 rounded-lg">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3 w-full md:w-auto">
                                    <?php if (!empty($t['attachment'])): ?>
                                        <a href="<?= BASE_URL ?>/uploads/tasks/<?= htmlspecialchars($t['attachment']) ?>" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2.5 rounded-xl transition" title="Lampiran">
                                            📎
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="syncToCalendar(<?= $t['id'] ?>)" class="bg-green-50 hover:bg-green-100 text-green-600 p-2.5 rounded-xl transition shadow-sm border border-green-100" title="Simpan ke Google Calendar">
                                        📅
                                    </button>
                                    <button onclick="toggleTask(<?= $t['id'] ?>)" class="flex-1 md:flex-none text-center px-5 py-2.5 rounded-xl font-bold transition shadow-lg flex items-center justify-center gap-2 <?= $isDone ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-blue-600 text-white hover:bg-blue-700 shadow-blue-500/30' ?>">
                                        <?= $isDone ? 'Batalkan' : 'Selesai' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleTask(taskId) {
            Swal.fire({
                title: 'Memproses...',
                didOpen: () => { Swal.showLoading() }
            });

            fetch('<?= BASE_URL ?>/api/toggle_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_id: taskId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: data.is_completed ? '🎉 Selesai!' : 'Kembali Aktif',
                        text: data.message,
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Oops!', text: data.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server' }));
        }

        function syncToCalendar(taskId) {
            Swal.fire({
                title: 'Menghubungkan ke Google...',
                text: 'Mohon tunggu sebentar',
                didOpen: () => { Swal.showLoading() }
            });

            fetch('<?= BASE_URL ?>/api/sync_calendar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_id: taskId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersinkronisasi!',
                        text: 'Tugas berhasil ditambahkan ke kalender Google Anda.',
                        footer: `<a href="${data.link}" target="_blank" class="text-blue-600 font-bold underline">Buka Kalender</a>`
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Sinkronisasi Gagal', text: data.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.' }));
        }
    </script>
</body>
</html>