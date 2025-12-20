<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/KelasModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new KelasModel($pdo);

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak ditemukan");

$data = $model->find($id);
if (!$data) die("Kelas tidak ditemukan");

$prodi = $pdo->query("SELECT * FROM prodi")->fetchAll(PDO::FETCH_ASSOC);
$angkatan = $pdo->query("SELECT * FROM angkatan")->fetchAll(PDO::FETCH_ASSOC);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($model->update($id, $_POST['nama_kelas'], $_POST['prodi_id'], $_POST['angkatan_id'])) {
        header("Location: index.php?msg=updated");
        exit;
    } else {
        $error = "Gagal mengupdate kelas.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Kelas | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen p-8 flex items-center justify-center">

<div class="w-full max-w-lg bg-white p-8 rounded-3xl shadow-2xl">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Kelas</h2>
        <a href="index.php" class="text-gray-400 hover:text-red-500 transition">✕</a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Kelas</label>
            <input type="text" name="nama_kelas" required placeholder="Contoh: IF-2022-A" 
                   value="<?= htmlspecialchars($data['nama_kelas']) ?>"
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Program Studi</label>
            <select name="prodi_id" required 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih Prodi --</option>
                <?php foreach ($prodi as $p): ?>
                    <option value="<?= $p['id_prodi'] ?>" <?= $data['prodi_id'] == $p['id_prodi'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama_prodi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Angkatan</label>
            <select name="angkatan_id" required 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih Angkatan --</option>
                <?php foreach ($angkatan as $a): ?>
                    <option value="<?= $a['id_angkatan'] ?>" <?= $data['angkatan_id'] == $a['id_angkatan'] ? 'selected' : '' ?>>
                        <?= $a['tahun'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">
                Update Kelas
            </button>
            <a href="index.php" class="px-6 py-3 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 font-semibold transition">
                Batal
            </a>
        </div>
    </form>

</div>

</body>
</html>
