<?php
$current_page = basename($_SERVER['PHP_SELF']);
$uri = $_SERVER['REQUEST_URI'];

// Determine path prefix (for pages in subfolders like admin/user/)
// Use a more robust way to detect if we are in a subfolder of admin
$normalized_path = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$is_sub = basename(dirname($normalized_path)) !== 'admin';
$prefix = $is_sub ? '../' : './';
?>
<!-- Admin Sidebar - Premium Glass Theme -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 backdrop-blur-2xl bg-slate-900/80 border-r border-white/10 flex flex-col z-50 transform -translate-x-full md:translate-x-0 md:relative md:shadow-none transition-transform duration-300 ease-in-out shadow-2xl text-white">
    <!-- Header -->
    <div class="h-20 flex items-center px-6 border-b border-white/10 bg-gradient-to-r from-transparent via-white/5 to-transparent">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 border border-white/20">
                <span class="text-2xl">⚡</span>
            </div>
            <div>
                <h2 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300 leading-tight">TaskAcademy</h2>
                <p class="text-blue-400 text-[9px] font-extrabold uppercase tracking-[0.2em]">System Admin</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 scrollbar-thin scrollbar-thumb-white/10">
        
        <a href="<?= $prefix ?>dashboard_admin.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $current_page == 'dashboard_admin.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">🏠</span>
            <span class="text-sm">Dashboard</span>
        </a>

        <p class="text-[10px] font-bold text-slate-500 px-4 mt-8 mb-2 uppercase tracking-widest">Master Data</p>

        <a href="<?= $prefix ?>user/index.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= strpos($uri, '/user/') !== false ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">👥</span>
            <span class="text-sm">Pengguna</span>
        </a>
        
        <a href="<?= $prefix ?>mk/index.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= strpos($uri, '/mk/') !== false ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">📚</span>
            <span class="text-sm">Mata Kuliah</span>
        </a>

        <a href="<?= $prefix ?>kelas/index.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= strpos($uri, '/kelas/') !== false ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">🏢</span>
            <span class="text-sm">Kelas</span>
        </a>
        
        <a href="<?= $prefix ?>prodi/index.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= strpos($uri, '/prodi/') !== false ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">🏛️</span>
            <span class="text-sm">Program Studi</span>
        </a>

        <a href="<?= $prefix ?>angkatan/index.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= strpos($uri, '/angkatan/') !== false ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">📅</span>
            <span class="text-sm">Angkatan</span>
        </a>

        <p class="text-[10px] font-bold text-slate-500 px-4 mt-8 mb-2 uppercase tracking-widest">Akademik</p>

        <a href="<?= $prefix ?>assign_mahasiswa.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $current_page == 'assign_mahasiswa.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">✏️</span>
            <span class="text-sm">Peserta Kelas</span>
        </a>

        <a href="<?= $prefix ?>assign_dosen.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $current_page == 'assign_dosen.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">👨‍🏫</span>
            <span class="text-sm">Penugasan Dosen</span>
        </a>

        <p class="text-[10px] font-bold text-slate-500 px-4 mt-8 mb-2 uppercase tracking-widest">Laporan</p>
        
        <a href="<?= $prefix ?>analytics.php" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $current_page == 'analytics.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold border border-white/10' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium' ?>">
            <span class="text-lg group-hover:scale-110 transition">📊</span>
            <span class="text-sm">Analitik</span>
        </a>
    </nav>

</aside>

<!-- Mobile Header -->
<div class="md:hidden fixed top-0 left-0 right-0 h-16 bg-slate-900/90 backdrop-blur-md border-b border-white/10 z-40 flex items-center px-4 justify-between shadow-xl">
    <button id="mobileMenuOpen" class="p-2 -ml-2 text-slate-300 hover:text-white hover:bg-white/10 rounded-xl transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
    <div class="flex items-center gap-2">
         <div class="bg-blue-600/20 p-1.5 rounded-lg border border-blue-500/20">
             <span class="text-xl">⚡</span>
         </div>
         <span class="font-bold text-white tracking-tight">TaskAcademia</span>
    </div>
    <div class="w-8"></div>
</div>

<!-- Mobile Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-30 hidden transition-opacity duration-300 opacity-0 md:hidden"></div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openBtn = document.getElementById('mobileMenuOpen');

    function toggleSidebar() {
        const isHidden = sidebar.classList.contains('-translate-x-full');
        
        if (isHidden) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.add('opacity-100'), 10);
            document.body.style.overflow = 'hidden';
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('opacity-100');
            setTimeout(() => {
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }
    }

    if (openBtn) openBtn.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);
</script>