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
        // Refresh local data
        $task = $taskModel->find($id);
    } else {
        $error = "Gagal memperbarui tugas.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Tugas | TaskAcademia</title>
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
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-6 text-gray-800">

    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none z-0">
         <div class="absolute top-[20%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
         <div class="absolute bottom-[20%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <div class="w-full max-w-3xl glass rounded-3xl p-8 md:p-10 shadow-2xl relative z-10 my-10">
        
        <!-- Header with Back Button (Same as prodi_edit.php) -->
        <div class="mb-8">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-blue-200 hover:text-white mb-2 transition text-sm font-semibold group">
                 <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                 Kembali
            </a>
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-white">Edit Tugas</h1>
            </div>
            <p class="text-blue-200 text-sm mt-1">Perbarui detail tugas mahasiswa.</p>
        </div>

        <?php if ($success): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '<?= $success ?>',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'daftar_tugas.php';
                });
            </script>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Mata Kuliah</label>
                <select name="course_id" class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    <?php foreach($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id']==$task['course_id']? 'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Judul Tugas</label>
                <input type="text" name="title" value="<?= htmlspecialchars($task['task_title']) ?>" required
                       class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none transition font-semibold text-gray-800">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="5"
                          class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Deadline</label>
                <input type="datetime-local" name="deadline" value="<?= str_replace(' ', 'T', substr($task['deadline'],0,16)) ?>"
                       class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            </div>

            <div class="pt-6 flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1">
                    Simpan Perubahan
                </button>
                <a href="daftar_tugas.php" class="px-8 py-4 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition bg-white/50">
                    Batal
                </a>
            </div>

        </form>    </div>

</body>
</html>
