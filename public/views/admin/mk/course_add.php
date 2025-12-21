<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/CourseModel.php";
require_once "../../../../app/Models/DosenCourseModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new CourseModel($pdo);
$dosenCourse = new DosenCourseModel($pdo);

// Ambil daftar dosen (users with role='dosen') - Assuming column 'nama' exists
$users = $pdo->query("SELECT id, nama FROM users WHERE role='dosen'")->fetchAll(PDO::FETCH_ASSOC);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $semester = $_POST['semester'];
    $dosen_id = $_POST['dosen_id'];

    if ($model->create($name, $semester)) {
        // Ambil ID course yang baru dibuat
        $newCourseId = $pdo->lastInsertId();

        // Assign dosen jika dipilih
        if (!empty($dosen_id)) {
            $dosenCourse->assign($dosen_id, $newCourseId);
        }

        header("Location: index.php?msg=created");
        exit;
    } else {
        $error = "Gagal membuat mata kuliah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mata Kuliah | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen p-8 flex items-center justify-center font-outfit">

    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <div class="w-full max-w-xl glass p-10 rounded-[2.5rem] shadow-2xl relative z-10 border border-white/20 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-purple-500 via-indigo-500 to-blue-500"></div>

        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Tambah Matakuliah</h2>
                <p class="text-blue-300/60 text-sm mt-1">Daftarkan mata kuliah baru dalam kurikulum.</p>
            </div>
            <a href="index.php" class="w-10 h-10 rounded-xl glass flex items-center justify-center text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all border border-white/10 group">
                <span class="group-hover:rotate-90 transition-transform duration-300">✕</span>
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 text-red-300 p-4 rounded-2xl mb-8 text-sm border border-red-500/20 flex items-center gap-3">
                <span class="text-xl">⚠️</span> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Nama Mata Kuliah</label>
                <input type="text" name="name" required placeholder="Contoh: Pemrograman Web Lanjut" 
                       class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-blue-300/30 border-white/10 transition-all">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Semester</label>
                    <div class="relative">
                        <select name="semester" required 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <option value="">Pilih Semester</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?= $i ?>">Semester <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Dosen Pengampu</label>
                    <div class="relative">
                        <select name="dosen_id" required 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] border border-white/10">
                    Simpan Mata Kuliah
                </button>
                <a href="index.php" class="px-8 py-4 rounded-2xl glass text-slate-300 hover:bg-white/20 font-bold transition flex items-center border border-white/10">
                    Batalkan
                </a>
            </div>
        </form>
    </div>

</body>
</html>
