<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../app/config/config.php";
require_once "../../../app/config/database.php";
require_once "../../../app/Models/EnrollmentModel.php";

$db = new Database();
$pdo = $db->connect();
$enrollModel = new EnrollmentModel($pdo);

// Fetch students and classes for dropdowns
$students = $pdo->query("SELECT id, nama FROM users WHERE role='mahasiswa' ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT id_kelas, nama_kelas FROM class ORDER BY nama_kelas")->fetchAll(PDO::FETCH_ASSOC);

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        // Handle Delete
        if ($enrollModel->delete($_POST['delete_id'])) {
            $msg = "Peserta berhasil dihapus dari kelas.";
        }
    } else {
        // Handle Enroll
        if ($enrollModel->enroll($_POST['student_id'], $_POST['class_id'])) {
            $msg = "Mahasiswa berhasil dimasukkan ke kelas.";
        } else {
            $error = "Mahasiswa sudah terdaftar di kelas ini atau terjadi kesalahan.";
        }
    }
}

$enrollments = $enrollModel->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Peserta Kelas | Admin</title>
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
                <a href="dashboard_admin.php" class="bg-indigo-100 hover:bg-indigo-200 p-2 rounded-xl transition">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold">Peserta Kelas</h1>
                    <p class="text-gray-600 text-sm">Assign mahasiswa ke dalam kelas.</p>
                </div>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="bg-green-500/20 border border-green-500/50 text-green-200 p-4 rounded-xl mb-6"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-xl mb-6"><?= $error ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Form Card -->
            <div class="glass p-8 rounded-3xl text-gray-800 h-fit">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-blue-800">
                    <span>➕</span> Tambah Peserta
                </h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Pilih Mahasiswa</label>
                        <select name="student_id" required 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                            <option value="">-- Mahasiswa --</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Pilih Kelas</label>
                        <select name="class_id" required 
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                            <option value="">-- Kelas --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id_kelas'] ?>"><?= htmlspecialchars($c['nama_kelas']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">
                        Assign Mahasiswa
                    </button>
                </form>
            </div>

            <!-- Table Card -->
            <div class="lg:col-span-2 glass p-8 rounded-3xl text-gray-800">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2 text-blue-800">
                    <span>📋</span> Daftar Peserta Terdaftar
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider font-semibold border-b">
                            <tr>
                                <th class="p-4">Kelas</th>
                                <th class="p-4">Mahasiswa</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($enrollments)): ?>
                                <?php foreach ($enrollments as $row): ?>
                                <tr class="hover:bg-blue-50/50 transition">
                                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($row['student_name']) ?></td>
                                    <td class="p-4 text-right">
                                        <form method="POST" onsubmit="return confirm('Hapus mahasiswa ini dari kelas?')">
                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                            <button class="text-red-500 hover:text-red-700 font-semibold text-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="p-8 text-center text-gray-500 italic">Belum ada peserta yang di-assign.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
