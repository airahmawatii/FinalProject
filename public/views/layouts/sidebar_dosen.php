<?php
/**
 * Sidebar Dosen - Tampilan Navigasi Kiri (Mobile & Desktop)
 * 
 * Fitur:
 * - Responsif (Toggle di Mobile, Tetap di Desktop)
 * - Auto Active State (Highlight menu berdasarkan halaman aktif)
 * - Indikator Status Sync Google Calendar
 */
require_once __DIR__ . '/../../../app/config/config.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Helper sederhana untuk mengecek halaman aktif
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
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 sidebar flex flex-col z-50 transform -translate-x-full md:translate-x-0 md:relative transition-all duration-300 ease-in-out bg-slate-900/80 backdrop-blur-2xl text-white shadow-2xl border-r border-white/10">
    
    <!-- Branding / Header -->
    <div class="h-20 flex items-center px-6 border-b border-white/10 bg-gradient-to-r from-transparent via-white/5 to-transparent">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white p-1 rounded-xl shadow-lg shadow-blue-500/20 border border-white/20 overflow-hidden">
                 <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo" class="w-full h-full object-cover">
            </div>
            <div>
                <h2 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300 leading-tight">TaskAcademia</h2>
                <p class="text-blue-400 text-[9px] font-extrabold uppercase tracking-[0.2em]">Lecturer Panel</p>
            </div>
        </div>

        <!-- Mobile Close Button -->
        <button id="close-sidebar-btn" class="md:hidden ml-auto text-white hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 scrollbar-thin scrollbar-thumb-white/10">
        
        <!-- Menu Items Dosen -->
        <?php 
        $current_path = basename($_SERVER['PHP_SELF']);
        foreach ([
            ['url' => BASE_URL . '/views/dosen/dashboard.php', 'icon' => '🏠', 'label' => 'Dashboard'],
            ['url' => BASE_URL . '/views/dosen/buat_tugas.php', 'icon' => '➕', 'label' => 'Buat Tugas'],
            ['url' => BASE_URL . '/views/dosen/daftar_tugas.php', 'icon' => '📋', 'label' => 'Daftar Tugas'],
            ['url' => BASE_URL . '/views/dosen/lihat_mahasiswa.php', 'icon' => '👥', 'label' => 'Mahasiswa'],
        ] as $item): 
            $isActive = $current_path == basename($item['url']);
            $activeClass = $isActive ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/20 border border-white/10 font-bold' : 'text-slate-400 hover:bg-white/5 hover:text-white font-medium';
        ?>
            <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition transform md:hover:scale-[1.02] group <?= $activeClass ?>">
                <span class="text-lg group-hover:scale-110 transition"><?= $item['icon'] ?></span>
                <span class="text-sm"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>



        <!-- Tombol Koneksi Google Calendar -->
        <!-- Logic: Cek apakah user sudah punya refresh_token di session. -->
        <!-- Jika BELUM, tampilkan tombol "Hubungkan". Jika SUDAH, tampilkan status "Terhubung". -->
        <div class="mt-8">
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
        const isMobile = () => window.innerWidth < 768;

        // Mobile Sidebar Toggle
        function toggleMobileSidebar() {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => { 
                    overlay.classList.add('hidden'); 
                    document.body.style.overflow = ''; 
                }, 300);
            }
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

        // Responsive Reset
        function handleResize() {
             if (!isMobile()) {
                // Desktop Mode - Reset mobile states
                overlay.classList.add('hidden', 'opacity-0');
                document.body.style.overflow = '';
                sidebar.classList.remove('-translate-x-full');
            } else {
                // Mobile Mode - Ensure sidebar is hidden by default
                if (!overlay.classList.contains('hidden')) {
                     sidebar.classList.remove('-translate-x-full');
                } else {
                     sidebar.classList.add('-translate-x-full');
                }
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call
    });
</script>

<style>
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
    aside#sidebar { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
