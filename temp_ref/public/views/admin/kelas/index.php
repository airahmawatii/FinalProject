<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/config.php";
require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/KelasModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new KelasModel($pdo);

// Get filter values
$filter_prodi = isset($_GET['prodi']) ? $_GET['prodi'] : '';
$filter_angkatan = isset($_GET['angkatan']) ? $_GET['angkatan'] : '';

// Fetch all Prodi and Angkatan for filter dropdowns
$prodis = $pdo->query("SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi")->fetchAll(PDO::FETCH_ASSOC);
$angkatans = $pdo->query("SELECT id_angkatan, tahun FROM angkatan ORDER BY tahun DESC")->fetchAll(PDO::FETCH_ASSOC);

// Build query with filters
$query = "
    SELECT k.*, p.nama_prodi, a.tahun
    FROM class k
    LEFT JOIN prodi p ON p.id_prodi = k.prodi_id
    LEFT JOIN angkatan a ON a.id_angkatan = k.angkatan_id
    WHERE 1=1
";
$params = [];

if ($filter_prodi) {
    $query .= " AND k.prodi_id = ?";
    $params[] = $filter_prodi;
}

if ($filter_angkatan) {
    $query .= " AND k.angkatan_id = ?";
    $params[] = $filter_angkatan;
}

$query .= " ORDER BY k.nama_kelas";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$kelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kelas | TaskAcademia</title>
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
                <a href="../dashboard_admin.php" class="bg-indigo-100 hover:bg-indigo-200 p-2 rounded-xl transition">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold">Data Kelas</h1>
                    <p class="text-gray-600 text-sm">Manajemen kelas mahasiswa.</p>
                </div>
            </div>
            <a href="kelas_add.php" class="bg-blue-500 hover:bg-blue-400 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                <span>+</span> Tambah Kelas
            </a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="bg-green-500/20 border border-green-500/50 text-green-200 p-4 rounded-xl mb-6">Kelas berhasil dihapus!</div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="glass p-6 rounded-2xl mb-6 text-gray-800">
            <form method="GET" class="flex gap-4 items-end flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Prodi</label>
                    <select name="prodi" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-800">
                        <option value="">Semua Prodi</option>
                        <?php foreach ($prodis as $p): ?>
                            <option value="<?= $p['id_prodi'] ?>" <?= $filter_prodi == $p['id_prodi'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama_prodi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Angkatan</label>
                    <select name="angkatan" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-800">
                        <option value="">Semua Angkatan</option>
                        <?php foreach ($angkatans as $a): ?>
                            <option value="<?= $a['id_angkatan'] ?>" <?= $filter_angkatan == $a['id_angkatan'] ? 'selected' : '' ?>>
                                Angkatan <?= htmlspecialchars($a['tahun']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white px-6 py-2 rounded-xl font-semibold shadow-lg transition">
                        Filter
                    </button>
                    <?php if ($filter_prodi || $filter_angkatan): ?>
                        <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-xl font-semibold transition inline-block">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="glass rounded-3xl overflow-hidden text-gray-800 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider font-semibold border-b">
                        <tr>
                            <th class="p-5">ID</th>
                            <th class="p-5">Nama Kelas</th>
                            <th class="p-5">Prodi</th>
                            <th class="p-5">Angkatan</th>
                            <th class="p-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($kelas)): ?>
                            <?php foreach ($kelas as $k): ?>
                            <tr class="hover:bg-blue-50/50 transition duration-150">
                                <td class="p-5 font-mono text-xs text-blue-400"><?= $k['id_kelas'] ?></td>
                                <td class="p-5">
                                    <div class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($k['nama_kelas']) ?></div>
                                </td>
                                <td class="p-5 text-gray-600">
                                    <?= htmlspecialchars($k['nama_prodi']) ?>
                                </td>
                                <td class="p-5">
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                        <?= $k['tahun'] ?>
                                    </span>
                                </td>
                                <td class="p-5 text-right flex gap-3 justify-end items-center">
                                    <a href="kelas_edit.php?id=<?= $k['id_kelas'] ?>" class="text-blue-600 hover:underline font-medium">Edit</a>
                                    <a href="kelas_delete.php?id=<?= $k['id_kelas'] ?>" 
                                       onclick="return confirm('Yakin hapus kelas ini?')" 
                                       class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-2 rounded-lg text-sm font-semibold transition">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center text-gray-500 italic">Belum ada data kelas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
