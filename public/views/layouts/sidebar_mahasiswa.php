<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Mahasiswa - Premium Glass Theme -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 backdrop-blur-2xl bg-slate-900/80 border-r border-white/10 flex flex-col z-50 transform -translate-x-full md:translate-x-0 md:relative md:shadow-none transition-transform duration-300 ease-in-out shadow-2xl text-white">
    <!-- Header Logo -->
    <div class="h-20 flex items-center px-6 border-b border-white/10 bg-gradient-to-r from-transparent via-white/5 to-transparent">
        <div class="flex items-center gap-3">
             <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 border border-white/20">
                <span class="text-2xl">🎓</span>
            </div>
            <div class="flex flex-col">
                <h2 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300 leading-tight">TaskAcademy</h2>
                <p class="text-cyan-400 text-[9px] font-extrabold uppercase tracking-[0.2em]">Mahasiswa</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 scrollbar-thin scrollbar-thumb-white/10">
        
        <p class="text-[10px] font-bold text-slate-500 px-4 mb-2 uppercase tracking-widest">Menu Utama</p>
        
        <a href="/FinalProject/public/views/mahasiswa/dashboard_mahasiswa.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $current_page == 'dashboard_mahasiswa.php' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 shadow-lg shadow-blue-600/40 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">🏠</span>
            <span class="text-sm">Dashboard</span>
        </a>
        
        <a href="daftar_tugas.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $current_page == 'daftar_tugas.php' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 shadow-lg shadow-blue-600/40 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">⚡</span>
            <span class="text-sm">Daftar Tugas</span>
        </a>

        <p class="text-[10px] font-bold text-slate-500 px-4 mt-8 mb-2 uppercase tracking-widest">Akun</p>

        <a href="/FinalProject/public/views/mahasiswa/profile.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $current_page == 'profile.php' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 shadow-lg shadow-blue-600/40 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">👤</span>
            <span class="text-sm">Profil Saya</span>
        </a>
    </nav>

    <!-- Profile Footer -->
    <div class="p-6 border-t border-white/10">
        <div class="bg-gradient-to-br from-blue-600/20 to-cyan-600/20 rounded-2xl p-4 border border-white/10 mb-4 shadow-inner">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold shadow-lg border border-white/20">
                    <?= strtoupper(substr($_SESSION['user']['nama'] ?? 'M', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Mahasiswa') ?></p>
                    <div class="flex flex-col gap-0.5">
                        <p class="text-[9px] text-cyan-300 font-extrabold uppercase tracking-wider"><?= htmlspecialchars($_SESSION['user']['nim'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
        <a href="/FinalProject/public/logout.php" class="flex items-center justify-center gap-2 w-full py-3 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 hover:border-red-500/40 text-red-400 hover:text-red-300 rounded-xl text-xs font-bold transition shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Sign Out
        </a>
    </div>
</aside>

<!-- Mobile Header -->
<div class="md:hidden fixed top-0 left-0 right-0 h-16 bg-gradient-to-r from-slate-900 to-slate-800 border-b border-white/10 z-40 flex items-center px-4 justify-between shadow-xl">
    <button onclick="toggleSidebar()" class="p-2 -ml-2 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
    <div class="flex items-center gap-2">
         <div class="bg-white/10 p-1.5 rounded-lg border border-white/10">
             <span class="text-xl">🎓</span>
         </div>
         <span class="font-bold text-white tracking-tight">TaskAcademia</span>
    </div>
    <div class="w-8"></div>
</div>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-30 hidden md:hidden backdrop-blur-sm transition-opacity opacity-0"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        const isHidden = sidebar.classList.contains('-translate-x-full');
        
        if (isHidden) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }
</script>