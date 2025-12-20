<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    die("Akses ditolak.");
}

require_once __DIR__ . '/../../../app/config/config.php';

$file = $_GET['file'] ?? '';
$file = basename($file); // Security: prevent path traversal

$filepath = __DIR__ . '/../../uploads/tasks/' . $file;
$fileurl = BASE_URL . '/uploads/tasks/' . $file;

if (!file_exists($filepath) || empty($file)) {
    die("File tidak ditemukan.");
}

$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
$is_previewable = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lihat Lampiran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex flex-col relative overflow-hidden">
    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none z-0">
         <div class="absolute top-[20%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
         <div class="absolute bottom-[20%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>
    <div class="relative z-10 flex-1 flex flex-col">

    <!-- Header with Back Button -->
    <div class="bg-white/10 backdrop-blur-md border-b border-white/10 p-4 sticky top-0 z-50 flex items-center gap-4 shadow-lg">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-white/70 hover:text-white transition text-sm font-semibold group">
             <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
             Kembali        </a>
        <h1 class="text-white font-bold text-lg truncate"><?= htmlspecialchars($file) ?></h1>
        <a href="<?= $fileurl ?>" download class="ml-auto bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl font-bold transition text-sm flex items-center gap-2">
            <span>⬇️</span> Download
        </a>
    </div>

    <!-- Content Viewer -->
    <div class="flex-1 bg-gray-800 p-4 flex justify-center items-center overflow-auto">
        <?php if ($is_previewable): ?>
            <?php if ($ext === 'pdf'): ?>
                <iframe src="<?= $fileurl ?>" class="w-full h-[85vh] rounded-xl shadow-2xl border border-gray-700 bg-white"></iframe>
            <?php else: ?>
                <img src="<?= $fileurl ?>" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl border border-gray-700 bg-white/5 object-contain">
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center text-white">
                <div class="text-6xl mb-4">📂</div>
                <p class="text-xl font-bold mb-2">File ini tidak dapat dipreview.</p>
                <p class="text-gray-400 mb-6">Silakan download untuk melihat isinya.</p>
                <a href="<?= $fileurl ?>" download class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-xl font-bold transition shadow-lg inline-block">
                    Download File
                </a>
            </div>
        <?php endif; ?>
    </div>

    </div>
</body>
</html>
