<?php
session_start();
require_once "../../../../app/config/database.php";

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db = new Database();
$pdo = $db->connect();

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID user tidak ditemukan.");
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("User tidak ditemukan.");
}

$error = "";

// Proses edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    try {
        if (isset($currentUser['status'])) {
             $stmt = $pdo->prepare("UPDATE users SET nama=?, email=?, role=?, status=? WHERE id=?");
             $stmt->execute([$nama, $email, $role, $status, $id]);
        } else {
             // Fallback if status column doesn't exist yet (though it should)
             $stmt = $pdo->prepare("UPDATE users SET nama=?, email=?, role=? WHERE id=?");
             $stmt->execute([$nama, $email, $role, $id]);
        }
        header("Location: index.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $error = "Update Gagal: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Pengguna | TaskAcademia</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen p-8 flex items-center justify-center">

<div class="w-full max-w-lg bg-white p-8 rounded-3xl shadow-2xl">

    <div class="mb-6">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-400 hover:text-blue-600 mb-2 transition text-sm font-semibold group">
             <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
             Kembali
        </a>
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Edit Pengguna</h2>
            <a href="index.php" class="text-gray-400 hover:text-red-500 transition">✕</a>
        </div>    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Lengkap</label>
            <input type="text" name="nama" required placeholder="Nama User" 
                   value="<?= htmlspecialchars($currentUser['nama']) ?>" 
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
            <input type="email" name="email" required placeholder="Email" 
                   value="<?= htmlspecialchars($currentUser['email']) ?>" 
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Role</label>
                <select name="role" required 
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    <option value="admin" <?= $currentUser['role']=='admin'?'selected':'' ?>>Admin</option>
                    <option value="dosen" <?= $currentUser['role']=='dosen'?'selected':'' ?>>Dosen</option>
                    <option value="mahasiswa" <?= $currentUser['role']=='mahasiswa'?'selected':'' ?>>Mahasiswa</option>
                </select>
            </div>
            
            <?php if(isset($currentUser['status'])): ?>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Status Akun</label>
                <select name="status" required 
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    <option value="active" <?= $currentUser['status']=='active'?'selected':'' ?>>Active (Bisa Login)</option>
                    <option value="pending" <?= $currentUser['status']=='pending'?'selected':'' ?>>Pending (Ditolak)</option>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="status" value="active">
            <?php endif; ?>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">
                Update Data
            </button>
            <a href="index.php" class="px-6 py-3 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 font-semibold transition">
                Batal
            </a>
        </div>
    </form>

</div>
</body>
</html>
