<?php
require_once __DIR__ . '/../../../app/config/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
// Helper function seperti di admin
if (!function_exists('isActive')) {
    function isActive($path) {
        global $current_page;
        return $current_page == basename($path);
    }
}
?>
<!-- Mobile Menu Button -->
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-[60] p-3 bg-slate-900/90 backdrop-blur-md rounded-xl text-white md:hidden shadow-lg transition-all active:scale-95 border border-white/10">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
</button>

<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-20 md:w-20 sidebar flex flex-col z-50 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out bg-slate-900/95 backdrop-blur-xl text-white group collapsed shadow-2xl border-r border-white/10">
    
    <!-- Branding / Header -->
    <div class="p-6 flex items-center justify-center h-24 border-b border-white/10 relative">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap transition-all duration-300">
            <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center shadow-lg shrink-0 overflow-hidden">
                 <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo" class="w-full h-full object-cover">
            </div>
            <div class="sidebar-text transition-all duration-300 opacity-100 group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 group-[.collapsed]:invisible">
                <h2 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300 tracking-tight">TaskAcademia</h2>
                <p class="text-[9px] uppercase text-blue-400 font-extrabold tracking-widest">Lecturer Panel</p>
            </div>
        </div>
        
        <!-- Desktop Toggle Button -->
        <button id="desktop-toggle-btn" class="hidden md:flex absolute -right-3 top-9 bg-slate-800 text-white w-6 h-6 items-center justify-center rounded-full shadow-md hover:bg-slate-700 transition-all z-50 border border-white/10 transform active:scale-95 group/btn">
            <svg class="w-4 h-4 transition-transform duration-500 transform group-[.collapsed]:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <!-- Mobile Close Button -->
        <button id="close-sidebar-btn" class="md:hidden absolute right-4 text-white hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-6 px-3 space-y-2 custom-scrollbar">
        
        <!-- Menu Items Dosen -->
        <?php foreach ([
            ['url' => BASE_URL . '/views/dosen/dashboard.php', 'icon' => '🏠', 'label' => 'Dashboard'],
            ['url' => BASE_URL . '/views/dosen/buat_tugas.php', 'icon' => '➕', 'label' => 'Buat Tugas'],
            ['url' => BASE_URL . '/views/dosen/daftar_tugas.php', 'icon' => '📋', 'label' => 'Daftar Tugas'],
            ['url' => BASE_URL . '/views/dosen/lihat_mahasiswa.php', 'icon' => '👥', 'label' => 'Mahasiswa'],
        ] as $item): 
            $isActive = $current_page == basename($item['url']);
            $activeClass = $isActive ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg border border-white/10' : 'hover:bg-white/5 text-slate-400 hover:text-white';
        ?>
            <a href="<?= $item['url'] ?>" class="flex items-center gap-4 px-3 py-3 rounded-xl transition-all duration-200 group/link relative <?= $activeClass ?>">
                <span class="text-xl min-w-[24px] text-center"><?= $item['icon'] ?></span>
                <span class="sidebar-text whitespace-nowrap font-medium text-sm transition-all group-[.collapsed]:opacity-0 group-[.collapsed]:w-0 overflow-hidden"><?= $item['label'] ?></span>
                 <!-- Tooltip -->
                 <div class="absolute left-14 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-[.collapsed]:group-hover/link:opacity-100 transition-opacity pointer-events-none z-50 whitespace-nowrap shadow-xl"><?= $item['label'] ?></div>
            </a>
        <?php endforeach; ?>



        <!-- Google Connect (Academic Sync) -->
        <div class="mt-8 px-2 transition-all duration-300 group-[.collapsed]:scale-0 group-[.collapsed]:opacity-0">
            <div class="h-[1px] bg-gradient-to-r from-transparent via-white/10 to-transparent my-6"></div>
            <?php if (empty($_SESSION['user']['refresh_token'])): ?>
                <div class="px-2">
                    <p class="text-[9px] font-black text-blue-400/60 uppercase tracking-widest mb-3 ml-1">Academic Sync</p>
                    <a href="<?= BASE_URL ?>/views/dosen/dosen-connect-google.php" 
                       class="flex items-center gap-3 bg-white/5 hover:bg-white/10 text-white px-4 py-3 rounded-2xl transition-all border border-white/5 group/gbtn shadow-lg">
                        <div class="bg-white p-1.5 rounded-lg shrink-0 group-hover/gbtn:scale-110 transition-transform">
                            <img src="https://www.gstatic.com/images/branding/product/1x/calendar_2020q4_48dp.png" alt="Google" class="w-4 h-4">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold leading-tight">Hubungkan</span>
                            <span class="text-[9px] text-blue-300/80 font-medium">Google Calendar</span>
                        </div>
                    </a>
                </div>
            <?php else: ?>
                <div class="px-2">
                    <div class="flex items-center gap-3 bg-green-500/5 text-green-300 px-4 py-3 rounded-2xl border border-green-500/20 shadow-inner">
                        <div class="relative">
                            <div class="w-2.5 h-2.5 rounded-full bg-green-500 animate-ping absolute inset-0 opacity-40"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-500 relative"></div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold leading-tight">Terhubung</span>
                            <span class="text-[9px] text-green-400/60 font-medium tracking-tight">Sync Otomatis Aktif</span>
                        </div>
                    </div>
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
        const mainContent = document.getElementById('main-content');
        const isMobile = () => window.innerWidth < 768;

        // JS Logic yang sama persis dengan Admin for consistency
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

        function toggleDesktopCollapse() {
            if (isMobile()) return;
            const isCollapsed = sidebar.classList.contains('collapsed');
            
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('w-72'); // Original widths from Admin
            sidebar.classList.toggle('w-20');
            sidebar.classList.toggle('md:w-72');
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
            localStorage.setItem('sidebar_collapsed_dosen', !isCollapsed);
        }

        // Auto Close on Mobile Link Click
        const navLinks = sidebar.querySelectorAll('nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    toggleMobileSidebar();
                }
            });
        });

        mobileMenuBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMobileSidebar();
        });
        
        overlay?.addEventListener('click', toggleMobileSidebar);
        closeSidebarBtn?.addEventListener('click', toggleMobileSidebar);
        desktopToggleBtn?.addEventListener('click', toggleDesktopCollapse);

        // Responsive Reset & Init
        function handleResize() {
             if (!isMobile()) {
                // Desktop Mode
                overlay.classList.add('hidden', 'opacity-0');
                document.body.classList.remove('overflow-hidden');
                sidebar.classList.remove('-translate-x-full');
                
                const savedState = localStorage.getItem('sidebar_collapsed_dosen');
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
                // Mobile Mode
                sidebar.classList.remove('w-20', 'md:w-20', 'w-72', 'md:w-72', 'collapsed'); // Reset fixed widths
                sidebar.classList.add('w-64'); // Fixed width for mobile
                
                if (mainContent) { 
                    mainContent.classList.remove('md:ml-20', 'md:ml-72'); 
                    mainContent.classList.add('ml-0');
                }

                if (!overlay.classList.contains('hidden')) {
                     sidebar.classList.remove('-translate-x-full');
                } else {
                     sidebar.classList.add('-translate-x-full');
                }
            }
        }

        window.addEventListener('resize', handleResize);
        
        // Initial Call
        setTimeout(handleResize, 100); // Delay slightly to ensure DOM ready
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
    #main-content { transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    aside#sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
