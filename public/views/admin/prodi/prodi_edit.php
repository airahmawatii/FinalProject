<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/ProdiModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new ProdiModel($pdo);

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak ditemukan");

$data = $model->find($id);
if (!$data) die("Prodi tidak ditemukan");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($model->update($id, $_POST['kode_prodi'], $_POST['nama_prodi'])) {
        header("Location: index.php?msg=updated");
        exit;
    } else {
        $error = "Gagal mengupdate prodi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Prodi | Admin</title>
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
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Edit Prodi</h2>
                <p class="text-blue-300/60 text-sm mt-1">Perbarui informasi Program Studi akademik.</p>
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
                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Kode Departemen / Prodi</label>
                <input type="text" name="kode_prodi" required placeholder="Contoh: IF" 
                       value="<?= htmlspecialchars($data['kode_prodi']) ?>"
                       class="w-full px-5 py-3.5 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-black placeholder-slate-400 border border-white/10 transition-all uppercase bg-white/90 backdrop-blur-sm">
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Nama Lengkap Program Studi</label>
                <input type="text" name="nama_prodi" required placeholder="Contoh: Informatika" 
                       value="<?= htmlspecialchars($data['nama_prodi']) ?>"
                       class="w-full px-5 py-3.5 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-black placeholder-slate-400 border border-white/10 transition-all bg-white/90 backdrop-blur-sm">
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
