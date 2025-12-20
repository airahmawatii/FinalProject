<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/config.php";
require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/CourseModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new CourseModel($pdo);

// Ambil semua mata kuliah
$courses = $model->getAll();

// Success message
$msg = "";
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created': $msg = "Mata Kuliah berhasil ditambahkan!"; break;
        case 'updated': $msg = "Mata Kuliah berhasil diperbarui!"; break;
        case 'deleted': $msg = "Mata Kuliah berhasil dihapus!"; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Mata Kuliah | TaskAcademia</title>
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
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen text-gray-800 p-8">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <a href="../dashboard_admin.php" class="bg-blue-100 hover:bg-blue-200 p-2 rounded-xl transition">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Data Mata Kuliah</h1>
                    <p class="text-gray-600 text-sm">Manajemen kurikulum dan semester.</p>
                </div>
            </div>
            <a href="course_add.php" class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                <span>+</span> Tambah Mata Kuliah
            </a>
        </div>

        <?php if ($msg): ?>
            <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-xl mb-6"><?= $msg ?></div>
        <?php endif; ?>

        <!-- Table Container -->
        <div class="glass rounded-3xl overflow-hidden text-gray-800 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider font-semibold border-b">
                        <tr>
                            <th class="p-5">ID</th>
                            <th class="p-5">Nama Mata Kuliah</th>
                            <th class="p-5">Semester</th>
                            <th class="p-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($courses)): ?>
                            <?php foreach ($courses as $c): ?>
                            <tr class="hover:bg-blue-50/50 transition duration-150">
                                <td class="p-5 font-mono text-xs text-blue-400"><?= $c['id'] ?></td>
                                <td class="p-5">
                                    <div class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($c['name']) ?></div>
                                </td>
                                <td class="p-5">
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                        Semester <?= isset($c['semester']) ? $c['semester'] : '-' ?>
                                    </span>
                                </td>
                                <td class="p-5 text-right flex gap-3 justify-end items-center">
                                    <a href="course_edit.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline font-medium">Edit</a>
                                    <a href="course_delete.php?id=<?= $c['id'] ?>" 
                                       onclick="return confirm('Hapus mata kuliah ini?')" 
                                       class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-2 rounded-lg text-sm font-semibold transition">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-10 text-center text-gray-500 italic">Belum ada mata kuliah yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
