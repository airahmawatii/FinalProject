<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/config.php";
require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/AngkatanModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new AngkatanModel($pdo);

$angkatan = $model->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Angkatan | TaskAcademia</title>
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
                <a href="../dashboard_admin.php" class="bg-blue/10 hover:bg-blue/20 p-2 rounded-xl transition">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold">Data Angkatan</h1>
                    <p class="text-blue-200 text-sm">Manajemen tahun angkatan.</p>
                </div>
            </div>
            <a href="angkatan_add.php" class="bg-blue-500 hover:bg-blue-400 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                <span>+</span> Tambah Angkatan
            </a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="bg-green-500/20 border border-green-500/50 text-green-200 p-4 rounded-xl mb-6">Angkatan berhasil dihapus!</div>
        <?php endif; ?>

        <!-- Table Container -->
        <div class="glass rounded-3xl overflow-hidden text-gray-800 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider font-semibold border-b">
                        <tr>
                            <th class="p-5">ID</th>
                            <th class="p-5">Tahun Angkatan</th>
                            <th class="p-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($angkatan)): ?>
                            <?php foreach ($angkatan as $a): ?>
                            <tr class="hover:bg-blue-50/50 transition duration-150">
                                <td class="p-5 font-mono text-xs text-blue-400"><?= $a['id_angkatan'] ?></td>
                                <td class="p-5">
                                    <div class="font-bold text-gray-800 text-lg"><?= $a['tahun'] ?></div>
                                </td>
                                <td class="p-5 text-right flex gap-3 justify-end items-center">
                                    <a href="angkatan_edit.php?id=<?= $a['id_angkatan'] ?>" class="text-blue-600 hover:underline font-medium">Edit</a>
                                    <a href="angkatan_delete.php?id=<?= $a['id_angkatan'] ?>" 
                                       onclick="return confirm('Yakin hapus?')" 
                                       class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-2 rounded-lg text-sm font-semibold transition">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-10 text-center text-gray-500 italic">Belum ada data angkatan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
