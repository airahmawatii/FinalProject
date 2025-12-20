<?php
session_start();
require_once "../../../../app/config/database.php";

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db = new Database();
$pdo = $db->connect();

$error = "";
$success = "";

// Proses tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $status = 'active'; // Admin created users are active by default

    // Basic Domain Validation (Optional enforcement)
    // if (strpos($email, '@mhs.ubpkarawang.ac.id') !== false && $role !== 'mahasiswa') {
    //     $error = "Email mahasiswa harus role Mahasiswa!";
    // } 

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, status) VALUES (?,?,?,?,?)");
            $stmt->execute([$nama, $email, $password, $role, $status]);
            header("Location: index.php?msg=created");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal menambah user: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tambah Pengguna | Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen p-8 flex items-center justify-center">

<div class="w-full max-w-lg bg-white p-8 rounded-3xl shadow-2xl">

<<<<<<< HEAD
    <div class="mb-6">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-400 hover:text-blue-600 mb-2 transition text-sm font-semibold group">
             <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
             Kembali
        </a>
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Tambah Pengguna Baru</h2>
        </div>
=======
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Pengguna Baru</h2>
        <a href="index.php" class="text-gray-600 hover:text-red-500 transition text-2xl">&times;</a>
>>>>>>> d18683958109ae9fe0244a71fdc030651f124058
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Lengkap</label>
            <input type="text" name="nama" required placeholder="Nama User" 
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
            <input type="email" name="email" id="emailInput" required placeholder="email@example.com" 
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            <p class="text-xs text-gray-400 mt-1">Role akan otomatis terpilih berdasarkan domain email.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
            <input type="password" name="password" required placeholder="••••••••" 
                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Role</label>
            <select name="role" id="roleSelect" required 
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih Role --</option>
                <option value="admin">Admin</option>
                <option value="dosen">Dosen</option>
                <option value="mahasiswa">Mahasiswa</option>
            </select>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">
                Simpan User
            </button>
            <a href="index.php" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 font-semibold transition">
                Batal
            </a>
        </div>
    </form>

</div>

<script>
    const emailInput = document.getElementById('emailInput');
    const roleSelect = document.getElementById('roleSelect');

    emailInput.addEventListener('input', function() {
        const email = this.value.toLowerCase();
        if (email.includes('@mhs.ubpkarawang.ac.id')) {
            roleSelect.value = 'mahasiswa';
        } else if (email.includes('@ubpkarawang.ac.id')) {
            roleSelect.value = 'dosen';
        }
        // Admin or others manual
    });
</script>

</body>
</html>
