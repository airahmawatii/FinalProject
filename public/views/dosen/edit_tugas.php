<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: /FinalProject/public/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';
require_once __DIR__ . '/../../../app/Models/CourseModel.php';

$db = new Database(); $pdo = $db->connect();
$taskModel = new TaskModel($pdo);
$courseModel = new CourseModel($pdo);

$id = $_GET['id'] ?? null;
$task = $taskModel->find($id);

if (!$task || $task['dosen_id'] != $_SESSION['user']['id']) {
    die("Tugas tidak ditemukan atau bukan milik Anda.");
}

$courses = $courseModel->getByDosen($_SESSION['user']['id']);

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $deadline = $_POST['deadline'];
    if (strpos($deadline,'T')!==false) $deadline = str_replace('T',' ',$deadline).':00';
    $course_id = $_POST['course_id'];

    if ($taskModel->update($id, $title, $desc, $deadline, $course_id)) {
        $success = "Tugas berhasil diperbarui!";
        $task = $taskModel->find($id);
    } else {
        $error = "Gagal memperbarui tugas.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas | TaskAcademia</title>
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

        <div class="p-6 md:p-10 relative z-10 max-w-4xl mx-auto pt-20 md:pt-10">
            <!-- Header section -->
            <div class="mb-10">
                <a href="daftar_tugas.php" class="inline-flex items-center gap-2 text-blue-200/50 hover:text-white mb-4 transition text-xs font-extrabold uppercase tracking-widest group">
                     <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                     Batal & Kembali
                </a>
                <h1 class="text-3xl font-bold mb-2">Edit Tugas ✏️</h1>
                <p class="text-blue-200">Perbarui rincian tugas untuk mahasiswa Anda.</p>
            </div>

            <?php if ($success): ?>
                <script>
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: '<?= $success ?>', showConfirmButton: false, timer: 1500
                    }).then(() => window.location.href = 'daftar_tugas.php');
                </script>
            <?php endif; ?>
            <?php if ($error): ?>
                <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error ?>' });</script>
            <?php endif; ?>

            <div class="glass rounded-[2rem] overflow-hidden border border-white/10 shadow-2xl">
                <form method="POST" class="p-8 md:p-12 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Mata Kuliah</label>
                            <div class="relative group">
                                <select name="course_id" required class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-900/40 border-white/10 appearance-none cursor-pointer font-bold transition-all">
                                    <?php foreach($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $c['id']==$task['course_id']? 'selected':'' ?> class="bg-slate-900"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Deadline</label>
                            <input type="datetime-local" name="deadline" value="<?= str_replace(' ', 'T', substr($task['deadline'],0,16)) ?>" required class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-bold transition-all">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Judul Tugas</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($task['task_title']) ?>" required class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-bold transition-all">
                    </div>
                    
                    <div class="space-y-3">
                        <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Deskripsi</label>
                        <textarea name="description" rows="6" class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-medium transition-all"><?= htmlspecialchars($task['description']) ?></textarea>
                    </div>

                    <div class="flex flex-col md:flex-row gap-5 pt-10">
                        <button type="submit" class="relative group flex-1">
                            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur opacity-30 group-hover:opacity-60 transition duration-1000 group-hover:duration-200"></div>
                            <div class="relative flex items-center justify-center gap-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-5 rounded-2xl font-black text-lg transition-all border border-white/20 active:scale-[0.98]">
                                <span class="text-2xl group-hover:rotate-12 transition-transform">💾</span>
                                <span>Perbarui Tugas</span>
                            </div>
                        </button>
                        <button type="button" onclick="window.history.back()" class="px-12 py-5 rounded-2xl glass text-blue-200 hover:bg-white/10 hover:text-white font-bold transition-all flex items-center justify-center border border-white/10 text-base">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
