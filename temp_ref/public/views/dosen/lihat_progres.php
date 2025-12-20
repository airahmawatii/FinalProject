<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

if (!$taskDetails) {
    die("Tugas tidak ditemukan.");
}

$students = $taskModel->getTaskProgress($taskId);
$total = count($students);
$completed = count(array_filter($students, fn($s) => $s['completed_at']));
$percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progres Tugas | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen text-gray-800 p-8">

    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <a href="daftar_tugas.php" class="bg-white/10 hover:bg-white/20 p-2 rounded-xl transition">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div class="text-white">
                    <h1 class="text-3xl font-bold">Progres Pengerjaan</h1>
                    <p class="text-blue-200 text-sm"><?= htmlspecialchars($taskDetails['task_title']) ?> - <?= htmlspecialchars($taskDetails['course_name']) ?></p>
                </div>
            </div>
            
            <div class="glass px-6 py-3 rounded-xl flex items-center gap-4">
                <div>
                    <span class="block text-xs uppercase text-gray-500 font-bold">Completion</span>
                    <span class="text-2xl font-bold text-blue-600"><?= $percentage ?>%</span>
                </div>
                <div class="w-16 h-16 relative">
                    <svg class="w-full h-full" viewBox="0 0 36 36">
                        <path class="text-gray-200" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" />
                        <path class="text-blue-600" stroke-dasharray="<?= $percentage ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="glass rounded-3xl overflow-hidden text-gray-800 shadow-2xl">
            <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-lg">Daftar Mahasiswa</h3>
                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"><?= $completed ?> / <?= $total ?> Selesai</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-white text-gray-500 uppercase text-xs tracking-wider font-semibold border-b">
                        <tr>
                            <th class="p-5">Nama Mahasiswa</th>
                            <th class="p-5">Status</th>
                            <th class="p-5 text-right">Waktu Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $s): ?>
                            <tr class="hover:bg-blue-50/50 transition duration-150">
                                <td class="p-5">
                                    <div class="font-bold text-gray-800"><?= htmlspecialchars($s['nama']) ?></div>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($s['email']) ?></div>
                                </td>
                                <td class="p-5">
                                    <?php if ($s['completed_at']): ?>
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                            ✅ Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">
                                            ❌ Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5 text-right text-sm text-gray-500">
                                    <?= $s['completed_at'] ? date('d M Y, H:i', strtotime($s['completed_at'])) : '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="p-8 text-center text-gray-500 italic">Belum ada mahasiswa di kelas ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
