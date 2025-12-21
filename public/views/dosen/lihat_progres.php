<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);

$taskId = $_GET['id'] ?? null;
if (!$taskId) {
    die("Task ID not provided.");
}

// Get Task Details
$stmt = $pdo->prepare("SELECT t.*, c.name as course_name FROM tasks t JOIN courses c ON c.id = t.course_id WHERE t.id = ?");
$stmt->execute([$taskId]);
$taskDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$taskDetails || $taskDetails['dosen_id'] != $_SESSION['user']['id']) {
    die("Tugas tidak ditemukan atau akses ditolak.");
}

$students = $taskModel->getTaskProgress($taskId);
$total = count($students);
$completed = count(array_filter($students, fn($s) => $s['completed_at']));
$percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Tugas | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex text-white font-outfit">

    <!-- Include Shared Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full md:w-auto min-h-screen transition-all duration-300 md:ml-20">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[10%] right-[10%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[20%] left-[10%] w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Breadcrumbs -->
            <div class="mb-4">
                <a href="daftar_tugas.php" class="inline-flex items-center gap-2 text-blue-200/50 hover:text-white transition text-xs font-extrabold uppercase tracking-widest group">
                     <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                     Kembali ke Daftar Tugas
                </a>
            </div>

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
                <div>
                     <h1 class="text-3xl font-bold mb-2 text-white"><?= htmlspecialchars($taskDetails['task_title']) ?></h1>
                     <p class="text-blue-200 font-medium">Mata Kuliah: <?= htmlspecialchars($taskDetails['course_name']) ?></p>
                </div>
                
                <!-- Quick Progress Summary -->
                <div class="glass px-8 py-4 rounded-3xl flex items-center gap-6 border-white/10 shadow-xl">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-extrabold text-blue-300 uppercase tracking-widest opacity-60">Completion Rate</span>
                        <span class="text-3xl font-black text-white"><?= $percentage ?>%</span>
                    </div>
                    <div class="w-14 h-14 relative flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <circle class="text-white/5" stroke="currentColor" stroke-width="4" fill="transparent" r="16" cx="18" cy="18"/>
                            <circle class="text-blue-500" stroke="currentColor" stroke-width="4" fill="transparent" r="16" cx="18" cy="18" 
                                    stroke-dasharray="100" stroke-dashoffset="<?= 100 - $percentage ?>" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="glass p-5 rounded-2xl border-white/10">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Total Mahasiswa</p>
                    <p class="text-2xl font-black"><?= $total ?></p>
                </div>
                <div class="glass p-5 rounded-2xl border-emerald-500/20 bg-emerald-500/5">
                    <p class="text-[10px] font-extrabold text-emerald-400 uppercase tracking-widest mb-1">Sudah Submit</p>
                    <p class="text-2xl font-black text-emerald-100"><?= $completed ?></p>
                </div>
                <div class="glass p-5 rounded-2xl border-red-500/20 bg-red-500/5">
                    <p class="text-[10px] font-extrabold text-red-400 uppercase tracking-widest mb-1">Belum Submit</p>
                    <p class="text-2xl font-black text-red-100"><?= $total - $completed ?></p>
                </div>
                <div class="glass p-5 rounded-2xl border-blue-500/20 bg-blue-500/5">
                    <p class="text-[10px] font-extrabold text-blue-400 uppercase tracking-widest mb-1">Tenggat</p>
                    <p class="text-xs font-extrabold uppercase mt-1 text-slate-200"><?= date('d M, H:i', strtotime($taskDetails['deadline'])) ?></p>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="glass rounded-[2rem] overflow-hidden border border-white/10 shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/5 text-blue-300 uppercase text-[10px] tracking-[0.2em] font-extrabold border-b border-white/10">
                                <th class="py-6 px-8">Nama Mahasiswa</th>
                                <th class="py-6 px-8 text-center">Status</th>
                                <th class="py-6 px-8 text-right">Waktu Submit</th>
                            </tr>
                        </thead>
                        <tbody class="text-blue-100 text-sm font-medium">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $s): ?>
                                    <tr class="border-b border-white/[0.03] hover:bg-white/[0.05] transition-all group">
                                        <td class="py-5 px-8">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 border border-blue-500/20 flex items-center justify-center text-blue-400 font-bold group-hover:scale-110 transition-transform shadow-inner">
                                                    <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-white group-hover:text-blue-300 transition-all"><?= htmlspecialchars($s['nama']) ?></span>
                                                    <span class="text-[10px] text-blue-200/40 uppercase tracking-widest"><?= htmlspecialchars($s['email']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-5 px-8 text-center">
                                            <?php if ($s['completed_at']): ?>
                                                <span class="inline-flex items-center gap-2 bg-emerald-500/10 text-emerald-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Selesai
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-2 bg-red-500/10 text-red-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Belum
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-5 px-8 text-right font-mono text-xs text-slate-400 lowercase">
                                            <?= $s['completed_at'] ? date('d M Y, H:i', strtotime($s['completed_at'])) : '--:--' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="py-20 text-center font-bold text-slate-500 italic opacity-50">
                                        Belum ada mahasiswa terdaftar di kelas ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
