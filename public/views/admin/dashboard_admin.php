<?php
session_start();

require_once __DIR__ . '/../../../app/config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$db = new Database();
$pdo = $db->connect();

// Stats Queries
$totalUsers = $pdo->query("SELECT COUNT(*) AS total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];
$totalDosen = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role='dosen'")->fetch(PDO::FETCH_ASSOC)['total'];
$totalMahasiswa = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role='mahasiswa'")->fetch(PDO::FETCH_ASSOC)['total'];
$totalTasks = $pdo->query("SELECT COUNT(*) AS total FROM tasks WHERE deadline >= CURDATE()")->fetch(PDO::FETCH_ASSOC)['total'];

// Pending Users
require_once __DIR__ . '/../../../app/Models/UserModel.php';
$userModel = new UserModel($pdo);
$pendingUsers = $userModel->getPendingUsers();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex font-outfit text-white">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 w-full min-h-screen transition-all relative">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>
        
        <div class="p-6 md:p-10 max-w-7xl mx-auto pt-20 md:pt-10 relative z-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Dashboard Admin</h1>
                     <p class="text-blue-200">Selamat datang kembali, <span class="font-bold text-white"><?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'])[0]) ?></span>!</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Online Badge -->
                    <div class="glass px-4 py-2 rounded-full flex items-center gap-2 text-sm text-blue-900 font-bold bg-white/80 backdrop-blur-sm hidden md:flex">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Online
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative group">
                        <button class="glass pl-2 pr-4 py-1.5 rounded-full flex items-center gap-3 text-left hover:bg-white/20 transition shadow-lg border border-white/10 ring-2 ring-blue-500/20">
                            <div class="w-10 h-10 rounded-full p-[2px] bg-gradient-to-br from-blue-400 to-indigo-600 shadow-inner">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['nama']) ?>&background=2563eb&color=fff&bold=true" 
                                     alt="Profile" class="w-full h-full rounded-full object-cover border-2 border-white/20">
                            </div>
                            <div class="hidden md:block text-right">
                                <p class="text-sm font-bold text-white leading-none"><?= htmlspecialchars(explode(' ', $_SESSION['user']['nama'])[0]) ?></p>
                                <p class="text-[10px] text-blue-200 uppercase font-semibold tracking-wider mt-0.5"><?= $_SESSION['user']['role'] ?></p>
                            </div>
                            <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 top-full mt-2 w-48 opacity-0 translate-y-2 pointer-events-none group-hover:opacity-100 group-hover:translate-y-0 group-hover:pointer-events-auto transition-all duration-300 z-50">
                            <div class="glass rounded-2xl p-2 shadow-2xl border border-white/20 overflow-hidden bg-slate-900/90 backdrop-blur-xl">
                                <a href="../../logout.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/logout">
                                    <span class="text-lg group-hover/logout:rotate-12 transition-transform">🚪</span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <!-- Total Users -->
                <div class="glass p-6 rounded-3xl border border-white/20 hover:bg-white/20 transition-all shadow-xl group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-2xl shadow-lg shadow-blue-500/20 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-300">Total Users</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-4xl font-bold text-white"><?= $totalUsers ?></h3>
                        <span class="text-xs text-blue-400 font-medium">Pengguna</span>
                    </div>
                </div>

                <!-- Dosen -->
                <div class="glass p-6 rounded-3xl border border-white/20 hover:bg-white/20 transition-all shadow-xl group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-gradient-to-br from-purple-500 to-pink-600 text-white rounded-2xl shadow-lg shadow-purple-500/20 group-hover:scale-110 transition">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-purple-300">Dosen</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-4xl font-bold text-white"><?= $totalDosen ?></h3>
                        <span class="text-xs text-purple-400 font-medium">Staf</span>
                    </div>
                </div>

                <!-- Mahasiswa -->
                <div class="glass p-6 rounded-3xl border border-white/20 hover:bg-white/20 transition shadow-xl group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-2xl shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300">Mahasiswa</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-4xl font-bold text-white"><?= $totalMahasiswa ?></h3>
                        <span class="text-xs text-emerald-400 font-medium">Pelajar</span>
                    </div>
                </div>

                <!-- Active Tasks -->
                <div class="glass p-6 rounded-3xl border border-white/20 hover:bg-white/20 transition shadow-xl group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-gradient-to-br from-orange-500 to-red-600 text-white rounded-2xl shadow-lg shadow-orange-500/20 group-hover:scale-110 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-300">Tugas Aktif</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-4xl font-bold text-white"><?= $totalTasks ?></h3>
                        <span class="text-xs text-orange-400 font-medium">Deadline</span>
                    </div>
                </div>
            </div>

            <!-- Pending Users Section -->
            <?php if (!empty($pendingUsers)): ?>
            <div class="glass rounded-3xl p-8 border border-yellow-500/30 mb-10 shadow-xl">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-yellow-500/30 rounded-xl text-yellow-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Menunggu Persetujuan</h3>
                        <p class="text-sm text-yellow-200"><?= count($pendingUsers) ?> pengguna baru mendaftar.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <?php foreach ($pendingUsers as $pu): ?>
                    <div class="flex flex-col md:flex-row items-center justify-between p-4 bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 shadow-md hover:bg-white/10 transition gap-4">
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <div class="w-10 h-10 rounded-full bg-yellow-500/30 flex items-center justify-center text-yellow-300 font-bold text-sm">
                                <?= strtoupper(substr($pu['nama'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-bold text-white"><?= htmlspecialchars($pu['nama']) ?></p>
                                <p class="text-xs text-slate-400"><?= htmlspecialchars($pu['email']) ?></p>
                            </div>
                        </div>
                        <form action="approve_user.php" method="POST" class="flex gap-2 items-center w-full md:w-auto justify-end">
                            <input type="hidden" name="user_id" value="<?= $pu['id'] ?>">
                            <button type="submit" name="role" value="dosen" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">
                                Jadikan Dosen
                            </button>
                            <button type="submit" name="role" value="mahasiswa" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl transition">
                                Jadikan Mhs
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="glass rounded-3xl p-8 border border-white/20 shadow-xl">
                <h3 class="text-xl font-bold text-white mb-8 flex items-center gap-2">
                    <span>⚡</span> Jalan Pintas
                </h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="user/user_add.php" class="p-6 bg-white/5 hover:bg-white/10 border border-white/10 rounded-3xl text-center transition-all group hover:-translate-y-2 shadow-lg">
                        <div class="w-16 h-16 bg-blue-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 group-hover:bg-blue-500/30 transition text-3xl">👤</div>
                        <p class="font-bold text-white text-sm">Tambah User</p>
                        <p class="text-[10px] text-blue-300 mt-1 uppercase tracking-widest font-bold">New Account</p>
                    </a>
                    <a href="mk/course_add.php" class="p-6 bg-white/5 hover:bg-white/10 border border-white/10 rounded-3xl text-center transition-all group hover:-translate-y-2 shadow-lg">
                        <div class="w-16 h-16 bg-purple-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 group-hover:bg-purple-500/30 transition text-3xl">📚</div>
                        <p class="font-bold text-white text-sm">Tambah MK</p>
                        <p class="text-[10px] text-purple-300 mt-1 uppercase tracking-widest font-bold">Curriculum</p>
                    </a>
                    <a href="kelas/kelas_add.php" class="p-6 bg-white/5 hover:bg-white/10 border border-white/10 rounded-3xl text-center transition-all group hover:-translate-y-2 shadow-lg">
                        <div class="w-16 h-16 bg-emerald-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 group-hover:bg-emerald-500/30 transition text-3xl">🎓</div>
                        <p class="font-bold text-white text-sm">Tambah Kelas</p>
                        <p class="text-[10px] text-emerald-300 mt-1 uppercase tracking-widest font-bold">Groups</p>
                    </a>
                    <a href="assign_mahasiswa.php" class="p-6 bg-white/5 hover:bg-white/10 border border-white/10 rounded-3xl text-center transition-all group hover:-translate-y-2 shadow-lg">
                        <div class="w-16 h-16 bg-orange-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 group-hover:bg-orange-500/30 transition text-3xl">✏️</div>
                        <p class="font-bold text-white text-sm">Assign Mhs</p>
                        <p class="text-[10px] text-orange-300 mt-1 uppercase tracking-widest font-bold">Enrollment</p>
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
            showConfirmButton: false,
            confirmButtonColor: '#2563EB'
        });
        <?php unset($_SESSION['flash_message']); ?>
    </script>
    <?php endif; ?>

</body>
</html>

