<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/AngkatanModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new AngkatanModel($pdo);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($model->create($_POST['tahun'])) {
        header("Location: index.php?msg=created");
        exit;
    } else {
        $error = "Gagal membuat angkatan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Angkatan | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen p-8 flex items-center justify-center">

<div class="w-full max-w-lg bg-white p-8 rounded-3xl shadow-2xl">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Angkatan</h2>
        <a href="index.php" class="text-gray-400 hover:text-red-500 transition">✕</a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Tahun Angkatan</label>
            <input type="number" name="tahun" required placeholder="Contoh: 2023" min="2000" max="2100"
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">
                Simpan Angkatan
            </button>
             <a href="index.php" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 font-semibold transition">
                Batal
            </a>
        </div>
    </form>

</div>

</body>
</html>
