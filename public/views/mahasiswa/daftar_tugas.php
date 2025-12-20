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
        .sidebar { background: rgba(15, 23, 42, 0.95); } /* Slate 900 */
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-6 text-gray-800">
        
    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none z-0">
         <div class="absolute top-[10%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
         <div class="absolute bottom-[10%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <!-- Main Card Container -->
    <div class="w-full max-w-7xl glass rounded-3xl p-8 md:p-10 shadow-2xl relative z-10 my-10 min-h-[80vh]">
        
        <!-- Header with Back Button (Same as prodi_edit.php) -->
        <div class="mb-8">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-400 hover:text-blue-600 mb-2 transition text-sm font-semibold group">
                 <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                 Kembali
            </a>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                     <h1 class="text-3xl font-bold text-gray-800">Daftar Tugas</h1>
                     <p class="text-gray-500 text-sm mt-1">Semua tugas yang perlu kamu selesaikan.</p>
                </div>
                 <div class="flex gap-2">
                     <a href="export_tasks_pdf.php" target="_blank" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-md transition flex items-center gap-1">
                        <span>📄</span> Rekap PDF
                    </a>
                    <span class="bg-blue-100 text-blue-700 font-bold px-4 py-1.5 rounded-full text-sm"><?= count($tasks) ?> Tugas</span>
                </div>
            </div>
        </div>

        <!-- Task List -->
        <?php if (empty($tasks)): ?>
            <div class="text-center py-20 bg-white/50 rounded-3xl border border-dashed border-gray-300">
                <div class="text-6xl mb-4 grayscale opacity-50">✨</div>
                <p class="text-gray-600 font-bold text-lg">Belum ada tugas yang diberikan dosen.</p>
                <p class="text-sm text-gray-500">Nikmati waktu luangmu!</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($tasks as $t): ?>
                    <?php $isDone = $t['is_completed']; ?>
                    <div id="task-card-<?= $t['id'] ?>" class="bg-gray-50/90 backdrop-blur-sm border border-white/50 rounded-2xl p-6 relative group hover:bg-white hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 <?= $isDone ? 'opacity-75 grayscale-[50%]' : '' ?>">
                        <div class="flex justify-between items-start mb-3">
                            <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wide">
                                <?= htmlspecialchars($t['course_name']) ?>
                            </span>
                            <?php if($t['is_completed']): ?>
                                <span class="bg-green-100 text-green-700 p-1 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                            <?php endif; ?>
                        </div>
                        
                        <h4 class="text-lg font-bold text-gray-800 mb-2 leading-snug group-hover:text-blue-600 transition <?= $isDone ? 'line-through text-gray-400' : '' ?>">
                            <?= htmlspecialchars($t['task_title']) ?>
                        </h4>
                        
                        <p class="text-gray-500 text-xs mb-4 font-semibold uppercase tracking-wide">Dosen: <?= htmlspecialchars($t['dosen_name']) ?></p>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed bg-white/60 p-3 rounded-lg border border-gray-100"><?= nl2br(htmlspecialchars($t['description'])) ?></p>
                        
                        <?php if (!empty($t['attachment'])): ?>
                            <?php 
                            $ext = strtolower(pathinfo($t['attachment'], PATHINFO_EXTENSION));
                            $isPreviewable = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
                            $viewUrl = "view_attachment.php?file=" . urlencode($t['attachment']);
                            ?>
                            
                            <?php if ($isPreviewable): ?>
                                <a href="<?= $viewUrl ?>" 
                                   class="inline-flex items-center gap-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-2 rounded-lg text-xs font-bold transition mb-4 w-full justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Lihat Lampiran
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/uploads/tasks/<?= htmlspecialchars($t['attachment']) ?>" target="_blank"
                                   class="inline-flex items-center gap-2 text-red-600 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg text-xs font-bold transition mb-4 w-full justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Download Lampiran
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="pt-4 border-t border-dashed border-gray-200 flex justify-between items-center gap-2">
                            <div class="flex items-center text-red-500 font-bold text-xs">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <?= date('d M', strtotime($t['deadline'])) ?>
                            </div>
                            
                            <button onclick="syncToCalendar(<?= $t['id'] ?>)" 
                                    class="text-gray-500 hover:text-blue-600 font-bold px-3 py-2 rounded-lg transition bg-white border border-gray-200 shadow-sm"
                                    title="Simpan ke Google Calendar">
                                📅
                            </button>
                            <button onclick="toggleTask(<?= $t['id'] ?>)" 
                                    class="flex-1 text-xs font-bold px-3 py-2 rounded-lg transition text-center shadow-sm <?= $isDone ? 'bg-green-100 text-green-700' : 'bg-gray-800 text-white hover:bg-gray-700' ?>">
                                <?= $isDone ? 'Batalkan' : 'Selesai' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <script>
        // Sidebar logic removed.
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

        function syncToCalendar(taskId) {
            Swal.fire({
                title: 'Menyimpan ke Google Calendar...',
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
                        title: 'Berhasil!',
                        text: 'Tugas telah ditambahkan ke kalender Google Anda.',
                        footer: `<a href="${data.link}" target="_blank" class="text-blue-600 underline">Lihat di Kalender</a>`
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.' });
            });
        }
    </script>
</body>
</html>