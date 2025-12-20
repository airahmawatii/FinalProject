<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Menu Button -->
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 p-2 bg-slate-800/80 backdrop-blur-md rounded-xl text-white md:hidden shadow-lg transition-transform active:scale-95 border border-white/10">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
</button>

<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-20 md:w-20 sidebar backdrop-blur-xl border-r border-slate-700/50 flex flex-col z-40 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl md:shadow-none bg-slate-900 text-white group collapsed">
    
    <!-- Header -->
    <div class="p-6 border-b border-slate-700/50 flex justify-between items-center h-20 relative">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap transition-all duration-300">
            <!-- Logo Image -->
            <img src="<?= BASE_URL ?>/assets/img/logo_task_academia.jpg" alt="Logo" class="w-10 h-10 rounded-lg object-cover shadow-md">
            <div class="sidebar-text transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0">
                <h2 class="text-lg font-bold leading-tight">Task Academia</h2>
                <p class="text-xs text-slate-400">Dosen Panel</p>
            </div>
        </div>
        
        <!-- Desktop Collapse Button -->
        <button id="desktop-toggle-btn" class="hidden md:flex absolute -right-3 top-8 bg-blue-600 text-white p-1 rounded-full shadow-lg hover:bg-blue-500 transition-colors z-50 border border-slate-900">
            <svg class="w-4 h-4 transition-transform duration-300 transform group-[.collapsed]:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Mobile Close Button -->
        <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-6 px-3 space-y-2 custom-scrollbar">
        
        <?php foreach ([
            ['url' => BASE_URL . '/views/dosen/dashboard.php', 'icon' => '🏠', 'label' => 'Dashboard'],
            ['url' => BASE_URL . '/views/dosen/buat_tugas.php', 'icon' => '➕', 'label' => 'Buat Tugas'],
            ['url' => BASE_URL . '/views/dosen/daftar_tugas.php', 'icon' => '📋', 'label' => 'Daftar Tugas'],
            ['url' => BASE_URL . '/views/dosen/lihat_mahasiswa.php', 'icon' => '👥', 'label' => 'Mahasiswa'],
            ['url' => BASE_URL . '/views/dosen/profile.php', 'icon' => '👤', 'label' => 'Profil Saya'],
        ] as $item): 
            $isActive = $current_page == basename($item['url']);
            $activeClass = $isActive ? 'bg-blue-600 shadow-lg shadow-blue-500/30 text-white font-bold' : 'hover:bg-white/10 text-slate-300 font-medium';
        ?>
            <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= $activeClass ?>">
                <span class="text-xl min-w-[24px] text-center"><?= $item['icon'] ?></span>
                <span class="sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden"><?= $item['label'] ?></span>
                
                <!-- Tooltip for collapsed state -->
                <div class="absolute left-14 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity duration-200 pointer-events-none shadow-xl border border-slate-700 whitespace-nowrap z-50">
                    <?= $item['label'] ?>
                </div>
            </a>
        <?php endforeach; ?>

        <!-- Logout -->
        <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-3 px-3 py-3 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 font-medium transition mt-10 group/link relative">
            <span class="text-xl min-w-[24px] text-center">🚪</span>
            <span class="sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden">Logout</span>
             <!-- Tooltip -->
            <div class="absolute left-14 bg-red-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity duration-200 pointer-events-none shadow-xl border border-red-700 whitespace-nowrap z-50">
                Logout
            </div>
        </a>

        <!-- Google Connect Status -->
        <div class="mt-6 px-1 transition-all duration-300 group-[.collapsed]:hidden">
            <?php if (empty($_SESSION['user']['refresh_token'])): ?>
                <a href="<?= BASE_URL ?>/connect_google.php" class="flex items-center gap-2 text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg transition w-full justify-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" class="w-4 h-4">
                    <span>Hubungkan Kalender</span>
                </a>
            <?php else: ?>
                <div class="flex items-center gap-2 text-xs text-green-400 bg-green-500/10 px-3 py-2 rounded-lg border border-green-500/20 w-full justify-center">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span>Google Terhubung</span>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const closeSidebarBtn = document.getElementById('close-sidebar-btn');
        const desktopToggleBtn = document.getElementById('desktop-toggle-btn');
        // Select main content by ID directly
        const mainContent = document.getElementById('main-content'); 

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
            // Toggle Sidebar State
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('w-72');
            sidebar.classList.toggle('w-20');
            sidebar.classList.toggle('md:w-72');
            sidebar.classList.toggle('md:w-20');
            
            // Toggle Main Content Margin to fill space
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
