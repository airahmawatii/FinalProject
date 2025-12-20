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
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen p-8 flex items-center justify-center">

<div class="w-full max-w-lg bg-white p-8 rounded-3xl shadow-2xl">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Mata Kuliah</h2>
        <a href="index.php" class="text-gray-800 hover:text-red-500 transition text-2xl font-bold">&times;</a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Mata Kuliah</label>
            <input type="text" name="name" required placeholder="Contoh: Pemrograman Web" 
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Semester</label>
            <select name="semester" required 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih Semester --</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?= $i ?>">Semester <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Dosen Pengajar</label>
            <select name="dosen_id" required 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih Dosen --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>">
                        <?= htmlspecialchars($u['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">
                Simpan Mata Kuliah
            </button>
            <a href="index.php" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 font-semibold transition">
                Batal
            </a>
        </div>
    </form>

</div>

</body>
</html>
