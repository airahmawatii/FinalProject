<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

$db = new Database();
$pdo = $db->connect();

// Total user
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
$totalUser = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalUsers = $totalUser; // Alias for new dashboard

// Total dosen
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role='dosen'");
$dosenAktif = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalDosen = $dosenAktif; // Alias for new dashboard

// Total mahasiswa
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role='mahasiswa'");
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalMahasiswa = $mahasiswa; // Alias for new dashboard

// Total deadline aktif (belum lewat)
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM tasks WHERE deadline >= CURDATE()");
$deadlineAktif = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalTasks = $deadlineAktif; // Alias for new dashboard

// Pending users
require_once __DIR__ . '/../../../app/Models/UserModel.php';
$userModel = new UserModel($pdo);
$pendingUsers = $userModel->getPendingUsers();

// Aktivitas terbaru (5 terakhir)
$stmt = $pdo->query("SELECT keterangan, waktu, level FROM aktivitas ORDER BY waktu DESC LIMIT 5");
$aktivitasTerbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar-link {
            transition: all 0.3s;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex text-gray-800">

    <!-- Sidebar Integrated -->
    <?php include __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto transition-all duration-300 md:ml-72">
        <div class="p-6 md:p-10 pt-20 md:pt-10 max-w-7xl mx-auto">
            <!-- Header -->
            <header class="mb-10">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Dashboard Admin</h1>
                <p class="text-gray-600">Selamat datang, <?= htmlspecialchars($_SESSION['user']['nama']) ?>! Kelola sistem TaskAcademia dengan mudah.</p>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Total Users -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-indigo-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Total</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?= $totalUsers ?></h3>
                    <p class="text-sm text-gray-500 font-medium">Pengguna Terdaftar</p>
                </div>

                <!-- Dosen -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-blue-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Dosen</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?= $totalDosen ?></h3>
                    <p class="text-sm text-gray-500 font-medium">Dosen Aktif</p>
                </div>

                <!-- Mahasiswa -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-teal-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-3 py-1 rounded-full">Mahasiswa</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?= $totalMahasiswa ?></h3>
                    <p class="text-sm text-gray-500 font-medium">Mahasiswa Aktif</p>
                </div>

                <!-- Active Tasks -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-purple-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Tasks</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-1"><?= $totalTasks ?></h3>
                    <p class="text-sm text-gray-500 font-medium">Tugas Aktif</p>
                </div>
            </div>

            <!-- Pending Users Section -->
            <?php if (!empty($pendingUsers)): ?>
            <div class="bg-white rounded-2xl p-8 shadow-lg mb-10 border border-yellow-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Pengguna Menunggu Persetujuan</h3>
                        <p class="text-sm text-gray-500"><?= count($pendingUsers) ?> pengguna perlu di-approve</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <?php foreach ($pendingUsers as $pu): ?>
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border border-yellow-200 hover:shadow-md transition gap-4">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex flex-shrink-0 items-center justify-center text-white font-bold text-lg shadow-lg">
                                <?= strtoupper(substr($pu['nama'], 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-gray-800 truncate"><?= htmlspecialchars($pu['nama']) ?></p>
                                <p class="text-sm text-gray-600 truncate"><?= htmlspecialchars($pu['email']) ?></p>
                            </div>
                        </div>
                        <form action="<?= BASE_URL ?>/views/admin/approve_user.php" method="POST" class="flex gap-2 items-center w-full md:w-auto justify-end">
                            <input type="hidden" name="user_id" value="<?= $pu['id'] ?>">
                            
                            <button type="submit" name="role" value="dosen" 
                                class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md transition-all">
                                ✅ Dosen
                            </button>
                            
                            <button type="submit" name="role" value="mahasiswa" 
                                class="bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md transition-all">
                                ✅ Mhs
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <span class="text-2xl">⚡</span> Quick Actions
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="<?= BASE_URL ?>/views/admin/user/user_add.php" class="p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl hover:shadow-lg transition-all duration-300 text-center border border-indigo-100 group">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform">👤</div>
                        <p class="font-semibold text-gray-700">Tambah User</p>
                    </a>
                    <a href="<?= BASE_URL ?>/views/admin/mk/course_add.php" class="p-6 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl hover:shadow-lg transition-all duration-300 text-center border border-blue-100 group">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform">📚</div>
                        <p class="font-semibold text-gray-700">Tambah MK</p>
                    </a>
                    <a href="<?= BASE_URL ?>/views/admin/kelas/kelas_add.php" class="p-6 bg-gradient-to-br from-teal-50 to-emerald-50 rounded-xl hover:shadow-lg transition-all duration-300 text-center border border-teal-100 group">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform">🎓</div>
                        <p class="font-semibold text-gray-700">Tambah Kelas</p>
                    </a>
                    <a href="<?= BASE_URL ?>/views/admin/assign_mahasiswa.php" class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl hover:shadow-lg transition-all duration-300 text-center border border-purple-100 group">
                        <div class="text-4xl mb-3 group-hover:scale-110 transition-transform">✏️</div>
                        <p class="font-semibold text-gray-700">Assign Mahasiswa</p>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- SweetAlert Logic -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes($_SESSION['flash_message']) ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['flash_message']); ?>
    </script>
    <?php endif; ?>

</body>
</html>

