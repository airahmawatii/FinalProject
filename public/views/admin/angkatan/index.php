<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

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
        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex font-outfit text-white">
    <?php include __DIR__ . '/../../layouts/sidebar_admin.php'; ?>
    <main class="flex-1 min-h-screen relative">
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[100px]"></div>
        </div>
        <div class="p-6 md:p-10 max-w-7xl mx-auto pt-20 md:pt-10 relative z-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Data Angkatan</h1>
                     <p class="text-blue-200">Manajemen tahun angkatan mahasiswa aktif.</p>
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
                                <a href="../../../logout.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/logout">
                                    <span class="text-lg group-hover/logout:rotate-12 transition-transform">🚪</span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="glass border-green-500/30 text-green-300 p-4 rounded-xl mb-6">Angkatan berhasil dihapus!</div>
            <?php endif; ?>

            <!-- Action Bar -->
            <div class="flex justify-end mb-8">
                <a href="angkatan_add.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-2xl text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:scale-105 transition flex items-center gap-2 border border-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Angkatan Baru
                </a>
            </div>
            <div class="glass rounded-[2rem] overflow-hidden border border-white/20 shadow-2xl mb-10">
                <table class="w-full text-left">
                    <thead class="bg-gradient-to-r from-white/5 to-transparent border-b border-white/10">
                        <tr>
                            <th class="p-6 text-blue-300 font-bold uppercase tracking-wider text-[10px]">Portal ID</th>
                            <th class="p-6 text-blue-300 font-bold uppercase tracking-wider text-[10px]">Tahun Akademik</th>
                            <th class="p-6 text-right text-blue-300 font-bold uppercase tracking-wider text-[10px]">Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (!empty($angkatan)): ?>
                            <?php foreach ($angkatan as $a): ?>
                            <tr class="hover:bg-white/[0.03] transition-colors group">
                                <td class="p-6 font-mono text-[10px] text-blue-400 font-bold">
                                    <span class="bg-blue-500/10 px-2 py-1 rounded-lg border border-blue-500/20">#<?= $a['id_angkatan'] ?></span>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500/20 to-indigo-600/20 flex items-center justify-center text-blue-300 text-xl border border-white/10 group-hover:scale-110 transition">
                                            📅
                                        </div>
                                        <div class="font-bold text-white text-xl group-hover:text-blue-400 transition"><?= $a['tahun'] ?></div>
                                    </div>
                                </td>
                                <td class="p-6 text-right">
                                    <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                        <a href="angkatan_edit.php?id=<?= $a['id_angkatan'] ?>" class="glass bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 p-2.5 rounded-xl transition border border-blue-500/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        <a href="angkatan_delete.php?id=<?= $a['id_angkatan'] ?>" onclick="return confirm('Yakin hapus?')" class="glass bg-red-500/10 hover:bg-red-500/20 text-red-300 p-2.5 rounded-xl transition border border-red-500/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-20 text-center text-slate-500 italic">
                                    <div class="text-4xl mb-4">🌫️</div>
                                    <p class="font-medium">Belum ada data angkatan ditemukan.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
