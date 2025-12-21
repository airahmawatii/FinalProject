<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);
$tasks = $taskModel->getByStudent($_SESSION['user']['id']);

// Get enrolled courses for semester info
$stmt = $pdo->prepare("SELECT DISTINCT c.semester FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.student_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$semesters = $stmt->fetchAll(PDO::FETCH_COLUMN);

$user = $_SESSION['user'];
$photoUrl = !empty($user['photo']) ? BASE_URL . "/uploads/profiles/" . $user['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=2563eb&color=fff&bold=true";
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex text-gray-800">

    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 p-2 bg-slate-800 rounded-lg text-white md:hidden shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>

    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

    <!-- Sidebar (Same as Dashboard) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-72 backdrop-blur-2xl bg-slate-900/80 border-r border-white/10 flex flex-col z-40 transform -translate-x-full md:translate-x-0 md:relative md:shadow-none transition-transform duration-300 ease-in-out shadow-2xl text-white">
        <div class="p-8 border-b border-white/10 flex justify-between items-center bg-gradient-to-r from-transparent via-white/5 to-transparent">
            <div>
                <h2 class="text-2xl font-bold flex items-center gap-3 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">
                    <span class="text-3xl">🎓</span> TaskAcademy
                </h2>
                <p class="text-slate-400 text-[10px] mt-2 font-bold tracking-[0.2em] uppercase">Student Dashboard</p>
            </div>
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 scrollbar-thin scrollbar-thumb-white/10">
            <!-- Status Card -->
            <div class="mb-6 bg-gradient-to-br from-blue-600/20 to-indigo-600/20 rounded-2xl p-4 border border-white/10 mx-2 shadow-inner">
                <p class="text-[9px] text-blue-300 font-extrabold uppercase tracking-widest mb-1">Status Akademik</p>
                <p class="text-sm text-white font-bold flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    Semester Aktif: <?= !empty($semesters) ? max($semesters) : '1' ?>
                </p>
            </div>

            <a href="dashboard_mahasiswa.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white font-medium transition duration-300 border border-transparent hover:border-white/10 group">
                <span class="text-lg group-hover:scale-110 transition">🏠</span> Dashboard
            </a>
            
            <p class="text-[10px] font-bold text-slate-500 px-4 mt-8 mb-3 uppercase tracking-widest">Utama</p>
            <a href="daftar_tugas.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold transition transform md:hover:scale-[1.02] border border-white/10">
                <span>📝</span> Daftar Tugas
            </a>
            
        </nav>

    </aside>

    <!-- Main Content -->
    <main class="flex-1 relative overflow-y-auto w-full md:w-auto">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[10%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[10%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header (Same as Dashboard) -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Daftar Tugas</h1>
                     <p class="text-blue-200">Semua tugas yang perlu kamu selesaikan.</p>
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
                                <img src="<?= $photoUrl ?>" alt="Profile" class="w-full h-full rounded-full object-cover border-2 border-white/20">
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
                                <div class="border-t border-white/10 my-1"></div>
                                <a href="<?= BASE_URL ?>/logout.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/logout">
                                    <span class="text-lg group-hover/logout:rotate-12 transition-transform">🚪</span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="space-y-8">
                <div id="tugas" class="glass rounded-3xl p-8 shadow-xl">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-2xl font-bold flex items-center gap-3 text-gray-800">
                            <span>📝</span> Tugas Kuliah
                        </h3>
                        <span class="bg-blue-100 text-blue-700 font-bold px-4 py-1.5 rounded-full text-sm"><?= count($tasks) ?> Tugas</span>
                    </div>
                    
                    <?php if (empty($tasks)): ?>
                        <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <div class="text-5xl mb-4">✨</div>
                            <p class="text-gray-500 font-medium">Belum ada tugas yang diberikan dosen.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                            <?php foreach ($tasks as $t): ?>
                                <?php $isDone = $t['is_completed']; ?>
                                <div id="task-card-<?= $t['id'] ?>" class="bg-gray-50 border border-gray-100 rounded-2xl p-6 relative group hover:bg-white hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col <?= $isDone ? 'opacity-75 bg-green-50/50 grayscale-[50%]' : '' ?>">
                                    <div class="flex justify-between items-start mb-4">
                                        <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wide">
                                            <?= htmlspecialchars($t['course_name']) ?>
                                        </span>
                                        <?php if($t['is_completed']): ?>
                                            <span class="text-green-600 text-xl font-bold">✓</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h4 class="text-lg font-bold text-gray-800 mb-3 leading-snug group-hover:text-blue-600 transition <?= $isDone ? 'line-through text-gray-400' : '' ?>">
                                        <?= htmlspecialchars($t['task_title']) ?>
                                    </h4>
                                    
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-[10px] font-bold text-white">
                                            <?= substr($t['dosen_name'], 0, 1) ?>
                                        </div>
                                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide"><?= htmlspecialchars($t['dosen_name']) ?></p>
                                    </div>

                                    <div class="flex-1">
                                        <p class="text-gray-600 text-sm mb-5 line-clamp-3 leading-relaxed bg-white/50 p-4 rounded-xl border border-gray-100/50 italic"><?= nl2br(htmlspecialchars($t['description'])) ?></p>
                                    </div>
                                    
                                    <?php if (!empty($t['attachment'])): ?>
                                        <a href="/public/uploads/tasks/<?= htmlspecialchars($t['attachment']) ?>" target="_blank" 
                                           class="inline-flex items-center gap-2 text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-2.5 rounded-xl text-xs font-bold transition mb-4 w-full justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Download Lampiran
                                        </a>
                                    <?php endif; ?>
                                    
                                    <div class="pt-4 border-t border-dashed border-gray-200 flex justify-between items-center gap-3">
                                        <div class="flex items-center gap-1.5 <?= strtotime($t['deadline']) < time() + 86400 * 3 ? 'text-red-500' : 'text-slate-500' ?> font-bold text-xs bg-white px-2 py-1 rounded-lg border border-gray-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <?= date('d M, H:i', strtotime($t['deadline'])) ?>
                                        </div>
                                        
                                        <button onclick="toggleTask(<?= $t['id'] ?>)" 
                                                class="flex-1 text-xs font-bold px-4 py-2.5 rounded-xl transition text-center shadow-lg shadow-blue-500/10 <?= $isDone ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:shadow-blue-600/30' ?>">
                                            <?= $isDone ? 'Batalkan' : 'Tandai Selesai' ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const closeSidebarBtn = document.getElementById('close-sidebar-btn');

        function toggleSidebar() {
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
        if(mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
            closeSidebarBtn.addEventListener('click', toggleSidebar);
        }

        function toggleTask(taskId) {
            fetch('<?= BASE_URL ?>/api/toggle_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_id: taskId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); 
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            });
        }
    </script>
</body>
</html>