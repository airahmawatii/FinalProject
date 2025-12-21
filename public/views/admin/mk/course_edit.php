<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/CourseModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new CourseModel($pdo);

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak ditemukan");

// data MK
$data = $model->find($id);
if (!$data) die("Mata Kuliah tidak ditemukan");

// ambil daftar dosen
$users = $pdo->query("SELECT id, nama FROM users WHERE role='dosen'")->fetchAll(PDO::FETCH_ASSOC);

// ambil dosen pengajar dari tabel pivot (Use correct column: course_id)
$stmt = $pdo->prepare("SELECT dosen_id FROM dosen_courses WHERE course_id=?");
$stmt->execute([$id]);
$current_dosen = $stmt->fetchColumn();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update data MK
    if ($model->update($id, $_POST['name'], $_POST['semester'])) {
        
        // update dosen pengajar
        // Check if relation exists
        if ($current_dosen) {
            $upd = $pdo->prepare("UPDATE dosen_courses SET dosen_id=? WHERE course_id=?");
            $upd->execute([$_POST['dosen_id'], $id]);
        } else {
            // If mistakenly empty before, insert new
            $ins = $pdo->prepare("INSERT INTO dosen_courses (dosen_id, course_id) VALUES (?, ?)");
            $ins->execute([$_POST['dosen_id'], $id]);
        }

        header("Location: index.php?msg=updated");
        exit;
    } else {
        $error = "Gagal mengupdate mata kuliah.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Mata Kuliah | Admin</title>
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
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500"></div>

        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Edit Matakuliah</h2>
                <p class="text-blue-300/60 text-sm mt-1">Perbarui data mata kuliah dan dosen pengampu.</p>
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
                <input type="text" name="name" required placeholder="Nama Mata Kuliah" 
                       value="<?= htmlspecialchars($data['name']) ?>" 
                       class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 transition-all">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Semester</label>
                    <div class="relative">
                        <select name="semester" required 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <?php for ($i=1; $i<=8; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == $data['semester'] ? 'selected' : '' ?>>
                                    Semester <?= $i ?>
                                </option>
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
                                <option value="<?= $u['id'] ?>" <?= $u['id'] == $current_dosen ? 'selected' : '' ?>>
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
                    Update Perubahan
                </button>
                <a href="index.php" class="px-8 py-4 rounded-2xl glass text-slate-300 hover:bg-white/20 font-bold transition flex items-center border border-white/10">
                    Batalkan
                </a>
            </div>
        </form>
    </div>

</body>
</html>
