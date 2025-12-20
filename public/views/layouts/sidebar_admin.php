<?php
$current_page = basename($_SERVER['PHP_SELF']);
function isActive($path) {
    global $current_page;
    return $current_page == basename($path);
}
?>
<!-- Mobile Menu Button -->
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-[60] p-3 bg-[#543abb] rounded-xl text-white md:hidden shadow-lg transition-all active:scale-95 border border-white/20">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
</button>

<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-20 md:w-20 sidebar flex flex-col z-50 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out bg-[#3e2b85] text-white group collapsed shadow-2xl">
    
    <!-- Branding / Header -->
    <div class="p-6 flex items-center justify-center h-24 border-b border-white/10 relative">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap transition-all duration-300">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-lg shrink-0 overflow-hidden">
                 <img src="<?= BASE_URL ?>/assets/img/logo_task_academia.jpg" alt="Logo" class="w-full h-full object-cover">
            </div>
            <div class="sidebar-text transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 group-[.collapsed]:invisible">
                <h2 class="text-lg font-bold text-white tracking-tight">TaskAcademia</h2>
                <p class="text-[10px] uppercase text-indigo-200 tracking-wider">Admin Panel</p>
            </div>
        </div>
        
        <!-- Desktop Toggle Button -->
        <button id="desktop-toggle-btn" class="hidden md:flex absolute -right-3 top-9 bg-white text-[#3e2b85] w-6 h-6 items-center justify-center rounded-full shadow-md hover:bg-gray-100 transition-all z-50 border border-gray-100 transform active:scale-95 group/btn">
            <svg class="w-4 h-4 transition-transform duration-500 transform group-[.collapsed]:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Mobile Close Button -->
        <button id="close-sidebar-btn" class="md:hidden absolute right-4 text-white hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-6 px-3 space-y-2 custom-scrollbar">
        
        <a href="<?= BASE_URL ?>/views/admin/dashboard_admin.php" class="flex items-center gap-4 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= isActive('dashboard_admin.php') ? 'bg-[#543abb] text-white shadow-lg' : 'hover:bg-[#4a339b] text-indigo-100' ?>">
            <span class="text-xl min-w-[24px] text-center">🏠</span>
            <span class="sidebar-text whitespace-nowrap font-medium text-sm transition-all group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden">Dashboard</span>
             <!-- Tooltip for collapsed state -->
            <div class="absolute left-14 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap shadow-xl">Dashboard</div>
        </a>

        <div class="my-4 border-t border-white/10 group-[.collapsed]:mx-2"></div>
         <div class="px-3 mb-2 text-[10px] font-bold uppercase text-indigo-300 tracking-wider transition-opacity group-[.collapsed]:hidden">Menu Utama</div>
        
        <?php foreach ([
            'user/index.php' => ['icon' => '👥', 'label' => 'Users'],
            'mk/index.php' => ['icon' => '📚', 'label' => 'Mata Kuliah'],
            'kelas/index.php' => ['icon' => '🎓', 'label' => 'Kelas'],
            'prodi/index.php' => ['icon' => '🏫', 'label' => 'Prodi'],
            'angkatan/index.php' => ['icon' => '🗓️', 'label' => 'Angkatan'],
            'assign_mahasiswa.php' => ['icon' => '📝', 'label' => 'Enrollment'],
            'assign_dosen.php' => ['icon' => '👨‍🏫', 'label' => 'Penugasan'],
        ] as $file => $data): 
            $url = BASE_URL . '/views/admin/' . $file;
            $active = isActive($file);
        ?>
            <a href="<?= $url ?>" class="flex items-center gap-4 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= $active ? 'bg-[#543abb] text-white shadow-lg' : 'hover:bg-[#4a339b] text-indigo-100' ?>">
                <span class="text-xl min-w-[24px] text-center"><?= $data['icon'] ?></span>
                <span class="sidebar-text whitespace-nowrap font-medium text-sm transition-all group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden"><?= $data['label'] ?></span>
                 <!-- Tooltip -->
                 <div class="absolute left-14 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap shadow-xl"><?= $data['label'] ?></div>
            </a>
        <?php endforeach; ?>

        <!-- Laporan -->
        <p class="text-xs font-bold text-indigo-300 px-3 mt-6 mb-2 uppercase tracking-wider transition-all duration-300 group-[.collapsed]:hidden">Laporan</p>
        <a href="<?= BASE_URL ?>/views/admin/analytics.php" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= isActive('analytics.php') ? 'bg-[#543abb] text-white shadow-lg' : 'hover:bg-[#4a339b] text-indigo-100' ?>">
            <span class="text-xl min-w-[24px] text-center">📊</span>
            <span class="sidebar-text whitespace-nowrap font-medium text-sm transition-all group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden">Analitik & CSV</span>
             <div class="absolute left-14 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap shadow-xl">Analitik & CSV</div>
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
        const mainContent = document.getElementById('main-content');
        const isMobile = () => window.innerWidth < 768;

        // FIXED: Robust Mobile Toggle
        function toggleMobileSidebar() {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                document.body.classList.add('overflow-hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => { 
                    overlay.classList.add('hidden'); 
                    document.body.classList.remove('overflow-hidden'); 
                }, 300);
            }
        }

        // FIXED: Desktop Toggle with State Maintenance
        function toggleDesktopCollapse() {
            if (isMobile()) return;
            const isCollapsed = sidebar.classList.contains('collapsed');
            
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('w-72'); // Original width
            sidebar.classList.toggle('w-20');
            sidebar.classList.toggle('md:w-72'); // Original width
            sidebar.classList.toggle('md:w-20');
            
            if (mainContent) {
                if (isCollapsed) {
                    mainContent.classList.remove('md:ml-20');
                    mainContent.classList.add('md:ml-72');
                } else {
                    mainContent.classList.remove('md:ml-72');
                    mainContent.classList.add('md:ml-20');
                }
            }
            localStorage.setItem('sidebar_collapsed_admin', !isCollapsed);
        }

        mobileMenuBtn?.addEventListener('click', toggleMobileSidebar);
        overlay?.addEventListener('click', toggleMobileSidebar);
        closeSidebarBtn?.addEventListener('click', toggleMobileSidebar);
        desktopToggleBtn?.addEventListener('click', toggleDesktopCollapse);

        // Responsive Reset
        function handleResize() {
             if (!isMobile()) {
                overlay.classList.add('hidden', 'opacity-0');
                document.body.classList.remove('overflow-hidden');
                sidebar.classList.remove('-translate-x-full');
                
                const savedState = localStorage.getItem('sidebar_collapsed_admin');
                if (savedState === 'false') {
                     sidebar.classList.remove('collapsed', 'w-20', 'md:w-20');
                     sidebar.classList.add('w-72', 'md:w-72');
                     if (mainContent) { mainContent.classList.remove('md:ml-20'); mainContent.classList.add('md:ml-72'); }
                } else {
                     sidebar.classList.add('collapsed', 'w-20', 'md:w-20');
                     sidebar.classList.remove('w-72', 'md:w-72');
                     if (mainContent) { mainContent.classList.remove('md:ml-72'); mainContent.classList.add('md:ml-20'); }
                }
            } else {
                sidebar.classList.remove('w-20', 'md:w-20', 'w-72', 'md:w-72', 'collapsed');
                sidebar.classList.add('w-64');
                if (mainContent) { mainContent.classList.remove('md:ml-20', 'md:ml-72'); mainContent.classList.add('ml-0'); }

                if (!overlay.classList.contains('hidden')) {
                     sidebar.classList.remove('-translate-x-full');
                } else {
                     sidebar.classList.add('-translate-x-full');
                }
            }
        }

        window.addEventListener('resize', handleResize);
        setTimeout(handleResize, 100);
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }
    #main-content { transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    aside#sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
