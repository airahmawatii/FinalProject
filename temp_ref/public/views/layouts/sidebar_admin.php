<?php
$current_page = basename($_SERVER['PHP_SELF']);
// Determine active path checking
function isActive($path) {
    global $current_page;
    // Simple check for now, can be expanded if subdirs used
    return $current_page == $path;
}
?>
<!-- Mobile Menu Button -->
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 p-2 bg-indigo-900/80 backdrop-blur-md rounded-xl text-white md:hidden shadow-lg transition-transform active:scale-95 border border-white/20">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
</button>

<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-20 md:w-20 sidebar backdrop-blur-xl border-r border-white/10 flex flex-col z-40 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl md:shadow-none bg-gradient-to-b from-indigo-800 to-purple-900 text-white group collapsed">
    
    <!-- Header -->
    <div class="p-6 border-b border-white/10 flex justify-between items-center h-20 relative">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap transition-all duration-300">
            <!-- Logo Image -->
            <img src="<?= BASE_URL ?>/assets/img/logo_task_academia.jpg" alt="Logo" class="w-10 h-10 rounded-lg object-cover shadow-md">
            <div class="sidebar-text transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0">
                <h2 class="text-lg font-bold leading-tight">Task Academia</h2>
                <p class="text-xs text-indigo-200">Admin Panel</p>
            </div>
        </div>
        
        <!-- Desktop Collapse Button -->
        <button id="desktop-toggle-btn" class="hidden md:flex absolute -right-3 top-8 bg-indigo-500 text-white p-1 rounded-full shadow-lg hover:bg-indigo-400 transition-colors z-50 border border-indigo-900">
            <svg class="w-4 h-4 transition-transform duration-300 transform group-[.collapsed]:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Mobile Close Button -->
        <button id="close-sidebar-btn" class="md:hidden text-indigo-200 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-6 px-3 space-y-2 custom-scrollbar">
        
        <a href="<?= BASE_URL ?>/views/admin/dashboard_admin.php" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= isActive('dashboard_admin.php') ? 'bg-white/20 shadow-lg font-bold' : 'hover:bg-white/10 text-indigo-100' ?>">
            <span class="text-xl min-w-[24px] text-center">🏠</span>
            <span class="sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden">Dashboard</span>
             <div class="absolute left-14 bg-indigo-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity duration-200 pointer-events-none shadow-xl border border-indigo-700 whitespace-nowrap z-50">Dashboard</div>
        </a>

        <!-- Master Data -->
        <p class="text-xs font-bold text-indigo-300 px-3 mt-6 mb-2 uppercase tracking-wider transition-all duration-300 group-[.collapsed]:hidden">Master Data</p>
        
        <?php foreach ([
            BASE_URL . '/views/admin/user/index.php' => ['icon' => '👥', 'label' => 'Kelola Pengguna'],
            BASE_URL . '/views/admin/mk/index.php' => ['icon' => '📚', 'label' => 'Kelola MK'],
            BASE_URL . '/views/admin/kelas/index.php' => ['icon' => '🎓', 'label' => 'Kelola Kelas'],
            BASE_URL . '/views/admin/prodi/index.php' => ['icon' => '🏫', 'label' => 'Kelola Prodi'],
            BASE_URL . '/views/admin/angkatan/index.php' => ['icon' => '🗓️', 'label' => 'Kelola Angkatan'],
        ] as $url => $data): ?>
             <a href="<?= $url ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= isActive(basename($url)) ? 'bg-white/20 shadow-lg font-bold' : 'hover:bg-white/10 text-indigo-100' ?>">
                <span class="text-xl min-w-[24px] text-center"><?= $data['icon'] ?></span>
                <span class="sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden"><?= $data['label'] ?></span>
                <div class="absolute left-14 bg-indigo-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity duration-200 pointer-events-none shadow-xl border border-indigo-700 whitespace-nowrap z-50"><?= $data['label'] ?></div>
            </a>
        <?php endforeach; ?>

        <!-- Akademik -->
        <p class="text-xs font-bold text-indigo-300 px-3 mt-6 mb-2 uppercase tracking-wider transition-all duration-300 group-[.collapsed]:hidden">Akademik</p>
         <?php foreach ([
            BASE_URL . '/views/admin/assign_mahasiswa.php' => ['icon' => '🎓', 'label' => 'Peserta Kelas'],
            BASE_URL . '/views/admin/assign_dosen.php' => ['icon' => '👨‍🏫', 'label' => 'Penugasan Dosen'],
        ] as $url => $data): ?>
             <a href="<?= $url ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= isActive(basename($url)) ? 'bg-white/20 shadow-lg font-bold' : 'hover:bg-white/10 text-indigo-100' ?>">
                <span class="text-xl min-w-[24px] text-center"><?= $data['icon'] ?></span>
                <span class="sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden"><?= $data['label'] ?></span>
                <div class="absolute left-14 bg-indigo-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity duration-200 pointer-events-none shadow-xl border border-indigo-700 whitespace-nowrap z-50"><?= $data['label'] ?></div>
            </a>
        <?php endforeach; ?>

        <!-- Laporan -->
        <p class="text-xs font-bold text-indigo-300 px-3 mt-6 mb-2 uppercase tracking-wider transition-all duration-300 group-[.collapsed]:hidden">Laporan</p>
        <a href="<?= BASE_URL ?>/views/admin/analytics.php" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= isActive('analytics.php') ? 'bg-white/20 shadow-lg font-bold' : 'hover:bg-white/10 text-indigo-100' ?>">
            <span class="text-xl min-w-[24px] text-center">📊</span>
            <span class="sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden">Analitik & CSV</span>
             <div class="absolute left-14 bg-indigo-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity duration-200 pointer-events-none shadow-xl border border-indigo-700 whitespace-nowrap z-50">Analitik & CSV</div>
        </a>

        <!-- Logout -->
        <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-red-300 hover:bg-red-500/20 hover:text-red-100 font-medium transition mt-10 group/link relative">
            <span class="text-xl min-w-[24px] text-center">🚪</span>
            <span class="sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden">Logout</span>
             <!-- Tooltip -->
            <div class="absolute left-14 bg-red-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity duration-200 pointer-events-none shadow-xl border border-red-700 whitespace-nowrap z-50">
                Logout
            </div>
        </a>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const closeSidebarBtn = document.getElementById('close-sidebar-btn');
        const desktopToggleBtn = document.getElementById('desktop-toggle-btn');
        const mainContent = document.getElementById('main-content'); // Select by ID

        // Mobile Toggle
        function toggleMobileSidebar() {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        // Desktop Collapse Toggle
        function toggleDesktopCollapse() {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('w-72');
            sidebar.classList.toggle('w-20');
            sidebar.classList.toggle('md:w-72');
            sidebar.classList.toggle('md:w-20');

            // Toggle margin on main content
            if (mainContent) {
                mainContent.classList.toggle('md:ml-72');
                mainContent.classList.toggle('md:ml-20');
            }
        }

        if(mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleMobileSidebar);
            overlay.addEventListener('click', toggleMobileSidebar);
            closeSidebarBtn.addEventListener('click', toggleMobileSidebar);
        }

        if(desktopToggleBtn) {
            desktopToggleBtn.addEventListener('click', toggleDesktopCollapse);
        }

        // Initialize sidebar as collapsed on desktop
        if (window.innerWidth >= 768 && mainContent) {
            mainContent.classList.add('md:ml-20');
        }
    });
</script>

<style>
    /* Custom Scrollbar for Sidebar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.05);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.3);
    }
</style>
