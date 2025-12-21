<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);

$tasks = $taskModel->getByDosen($_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
        .sidebar { background: rgba(15, 23, 42, 0.95); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex text-white font-outfit">

    <!-- Success Message -->
    <?php if (isset($_GET['msg'])): ?>
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '<?= htmlspecialchars($_GET['msg'] === 'deleted' ? 'Tugas berhasil dihapus!' : $_GET['msg']) ?>',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: 'rgba(15, 23, 42, 0.95)',
                color: '#fff'
            });
        </script>
    <?php endif; ?>

    <!-- Include Shared Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full md:w-auto min-h-screen transition-all duration-300 md:ml-20">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Daftar Tugas</h1>
                     <p class="text-blue-200">Kelola dan pantau seluruh tugas yang telah Anda berikan.</p>
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
                                <a href="profile.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/profile">
                                    <span class="text-lg group-hover/profile:scale-110 transition-transform">👤</span>
                                    Profile
                                </a>
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

            <div class="flex justify-end mb-8">
                 <a href="buat_tugas.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 px-7 py-3.5 rounded-2xl font-bold text-white shadow-xl shadow-blue-500/20 hover:scale-[1.03] transition-all flex items-center gap-3 border border-white/10 active:scale-[0.98]">
                    <span class="text-xl">+</span> Buat Tugas Baru
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <?php if (empty($tasks)): ?>
                    <div class="glass p-20 text-center rounded-[2.5rem] border border-dashed border-white/20">
                        <div class="text-7xl mb-6">📂</div>
                        <h3 class="text-2xl font-bold text-white">Belum ada tugas</h3>
                        <p class="text-blue-200 mt-2 font-medium opacity-60">Mulai dengan membuat tugas baru untuk mahasiswa Anda.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $t): ?>
                        <div class="glass p-6 md:p-8 rounded-[2rem] flex flex-col md:flex-row justify-between items-center gap-6 hover:bg-white/[0.08] transition-all group border border-white/10">
                            <div class="flex-1 w-full">
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <span class="bg-blue-500/20 text-blue-300 text-[10px] font-bold px-4 py-1.5 rounded-full uppercase tracking-widest border border-blue-500/30"><?= htmlspecialchars($t['course_name']) ?></span>
                                    <span class="text-slate-400 text-xs flex items-center gap-1.5 font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                                        Dibuat: <?= date('d M Y', strtotime($t['created_at'])) ?>
                                    </span>
                                </div>
                                <h3 class="text-2xl font-extrabold mb-4 text-white group-hover:text-blue-400 transition-colors"><?= htmlspecialchars($t['task_title']) ?></h3>
                                <div class="flex items-center text-red-100 font-extrabold text-sm bg-red-500/20 px-4 py-2 rounded-xl border border-red-500/30 w-fit">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Tenggat: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <a href="lihat_progres.php?id=<?= $t['id'] ?>" class="flex-1 md:flex-none text-center bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-7 py-3 rounded-2xl font-bold transition-all shadow-xl shadow-blue-500/30 flex items-center justify-center gap-3 border border-white/10 active:scale-95">
                                    <span>👁️</span> Progress
                                </a>
                                <div class="flex gap-2">
                                    <a href="edit_tugas.php?id=<?= $t['id'] ?>" class="bg-white/5 hover:bg-white/10 text-white p-3 rounded-2xl transition-all border border-white/10 hover:border-blue-500/50" title="Edit">
                                        <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <button onclick="confirmDelete(<?= $t['id'] ?>)" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 p-3 rounded-2xl transition-all border border-red-500/20 hover:border-red-500" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data tugas akan dihapus selamanya!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#0f172a',
                color: '#fff',
                customClass: {
                    popup: 'rounded-3xl border border-white/10 backdrop-blur-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'hapus_tugas.php?id=' + id;
                }
            })
        }
    </script>
</body>
</html>
