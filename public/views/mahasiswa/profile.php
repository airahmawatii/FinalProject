<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: /FinalProject/public/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/UserModel.php';

$db = new Database(); 
$pdo = $db->connect();
$userModel = new UserModel($pdo);

$role = $_SESSION['user']['role'];
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $pw = $_POST['password'] ?? '';
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
         $uploadDir = __DIR__ . '/../../uploads/profiles/';
         if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
         $newFilename = "profile_" . $_SESSION['user']['id'] . "_" . time() . "." . strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
         if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFilename)) $photo = $newFilename;
    }
    $hashedPw = $pw ? password_hash($pw, PASSWORD_DEFAULT) : null;
    $userModel->updateProfile($_SESSION['user']['id'], $name, $hashedPw, $photo);
    $_SESSION['user'] = $userModel->findById($_SESSION['user']['id']);
    $success = "Profil berhasil diperbarui!";
}

$user = $_SESSION['user'];
$photoUrl = !empty($user['photo']) ? "/FinalProject/public/uploads/profiles/" . $user['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=2563eb&color=fff&bold=true";

// Get enrolled courses for semester info
$stmt = $pdo->prepare("SELECT DISTINCT c.semester FROM enrollments e JOIN courses c ON c.id = e.course_id WHERE e.student_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$semesters = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | TaskAcademia</title>
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
            <a href="daftar_tugas.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-slate-300 hover:bg-white/5 hover:text-white font-medium transition duration-300 border border-transparent hover:border-white/10 group">
                <span class="text-lg group-hover:scale-110 transition">📝</span> Daftar Tugas
            </a>
            
            <p class="text-[10px] font-bold text-slate-500 px-4 mt-8 mb-3 uppercase tracking-widest">Akun</p>
            <a href="profile.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-500/20 text-white font-bold transition transform md:hover:scale-[1.02] border border-white/10">
                <span>👤</span> Profil Saya
            </a>
            <a href="/FinalProject/public/logout.php" class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 font-medium transition mt-auto border border-transparent hover:border-red-500/10">
                <span>🚪</span> Logout
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

        <div class="p-6 md:p-10 relative z-10 max-w-4xl mx-auto pt-20 md:pt-10">
            <!-- Header (Same as Dashboard) -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Edit Profil</h1>
                     <p class="text-blue-200">Perbarui informasi personal dan foto profilmu.</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Online Badge -->
                    <div class="glass px-4 py-2 rounded-full flex items-center gap-2 text-sm text-blue-900 font-bold bg-white/80 backdrop-blur-sm hidden md:flex">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Online
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative group">
                        <a href="profile.php" class="glass pl-2 pr-4 py-1.5 rounded-full flex items-center gap-3 text-left hover:bg-white/20 transition shadow-lg border border-white/10 ring-2 ring-blue-500/20">
                            <div class="w-10 h-10 rounded-full p-[2px] bg-gradient-to-br from-blue-400 to-indigo-600 shadow-inner">
                                <img src="<?= $photoUrl ?>" 
                                     alt="Profile" class="w-full h-full rounded-full object-cover border-2 border-white/20">
                            </div>
                            <div class="hidden md:block text-right">
                                <p class="text-sm font-bold text-white leading-none"><?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?></p>
                                <p class="text-[10px] text-blue-200 uppercase font-semibold tracking-wider mt-0.5"><?= $user['role'] ?></p>
                            </div>
                            <svg class="w-4 h-4 text-blue-200 hidden md:block group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="glass p-4 rounded-2xl mb-6 bg-green-500/10 border-green-500/30 flex items-center gap-3 text-green-400">
                    <span>✅</span> <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- Profile Form Card -->
            <div class="glass rounded-[2.5rem] p-8 md:p-12 shadow-2xl">
                <form method="POST" enctype="multipart/form-data" class="space-y-8">
                    <!-- Photo Section -->
                    <div class="flex flex-col md:flex-row items-center gap-8 mb-10">
                        <div class="relative group cursor-pointer">
                            <div class="absolute -inset-1 bg-gradient-to-tr from-cyan-400 to-blue-600 rounded-full opacity-70 blur-md group-hover:opacity-100 transition duration-500"></div>
                            <img src="<?= $photoUrl ?>" class="relative w-36 h-36 rounded-full object-cover border-4 border-white/20 shadow-2xl transition transform group-hover:scale-105">
                            <label class="absolute bottom-1 right-1 bg-blue-600 text-white rounded-full p-2.5 shadow-lg cursor-pointer hover:bg-blue-500 transition border-2 border-white/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <input type="file" name="photo" class="hidden" onchange="document.querySelector('.relative.w-36.h-36.rounded-full.object-cover').src = window.URL.createObjectURL(this.files[0])">
                            </label>
                        </div>
                        <div class="flex-1 text-center md:text-left text-gray-800">
                             <h3 class="text-2xl font-bold mb-1"><?= htmlspecialchars($user['nama']) ?></h3>
                             <p class="text-slate-500 font-medium mb-4"><?= htmlspecialchars($user['email']) ?></p>
                             <div class="bg-blue-50 px-4 py-2 rounded-full inline-block text-blue-600 text-xs font-bold uppercase tracking-wider">
                                Role: <?= $user['role'] ?>
                             </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-700 ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['nama']) ?>" required 
                                   class="w-full px-5 py-4 rounded-2xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-gray-800 transition-all shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-700 ml-1">NIM (Nomor Induk Mahasiswa)</label>
                            <input type="text" value="<?= htmlspecialchars($user['nim'] ?? '-') ?>" disabled 
                                   class="w-full px-5 py-4 rounded-2xl bg-gray-100 border border-gray-200 text-slate-500 font-medium cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-700 ml-1">Program Studi</label>
                            <input type="text" value="<?= htmlspecialchars($user['nama_prodi'] ?? '-') ?>" disabled 
                                   class="w-full px-5 py-4 rounded-2xl bg-gray-100 border border-gray-200 text-slate-500 font-medium cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-700 ml-1">Email (Akun)</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled 
                                   class="w-full px-5 py-4 rounded-2xl bg-gray-100 border border-gray-200 text-slate-500 font-medium cursor-not-allowed">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 ml-1">Update Password</label>
                        <input type="password" name="password" placeholder="Masukkan password baru jika ingin diganti" 
                               class="w-full px-5 py-4 rounded-2xl bg-gray-50 border border-gray-200 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none font-medium text-gray-800 transition-all shadow-sm">
                    </div>

                    <div class="pt-8 border-t border-gray-100">
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-8 py-5 rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all transform hover:-translate-y-1 active:scale-[0.98] flex items-center justify-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan Akun
                        </button>
                    </div>
                </form>
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
    </script>
</body>
</html>