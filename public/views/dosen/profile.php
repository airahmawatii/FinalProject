<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/UserModel.php';

$db = new Database();
$pdo = $db->connect();
$userModel = new UserModel($pdo);

$success = "";
$error = "";

// Form Handling
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
    if ($userModel->updateProfile($_SESSION['user']['id'], $name, $hashedPw, $photo)) {
        // Refresh session data
        $_SESSION['user'] = $userModel->findById($_SESSION['user']['id']);
        $success = "Profil berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui profil.";
    }
}

// Always fetch fresh data to get NIDN/NIP/NIM etc
$user = $userModel->findById($_SESSION['user']['id']);
// Fallback if session user is needed directly
$_SESSION['user'] = $user;

$photoUrl = !empty($user['photo']) ? BASE_URL . "/uploads/profiles/" . $user['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=2563eb&color=fff&bold=true";
?>
<!DOCTYPE html>
<html lang="id">
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
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex text-white font-outfit">

    <!-- Shared Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full md:w-auto min-h-screen transition-all duration-300 md:ml-20">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-4xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
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
                        <button class="glass pl-2 pr-4 py-1.5 rounded-full flex items-center gap-3 text-left hover:bg-white/20 transition shadow-lg border border-white/10 ring-2 ring-blue-500/20">
                            <div class="w-10 h-10 rounded-full p-[2px] bg-gradient-to-br from-blue-400 to-indigo-600 shadow-inner">
                                <img src="<?= $photoUrl ?>" alt="Profile" class="w-full h-full rounded-full object-cover">
                            </div>
                            <div class="hidden md:block text-right">
                                <p class="text-sm font-bold text-white leading-none"><?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?></p>
                                <p class="text-[10px] text-blue-200 uppercase font-semibold tracking-wider mt-0.5"><?= $user['role'] ?></p>
                            </div>
                            <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 top-full mt-2 w-48 opacity-0 translate-y-2 pointer-events-none group-hover:opacity-100 group-hover:translate-y-0 group-hover:pointer-events-auto transition-all duration-300 z-50">
                            <div class="glass rounded-2xl p-2 shadow-2xl border border-white/20 overflow-hidden bg-slate-900/90 backdrop-blur-xl">
                                <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/profile">
                                    <span class="text-lg group-hover/profile:scale-110 transition-transform">👤</span>
                                    Profile
                                </a>
                                <a href="../../logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/logout">
                                    <span class="text-lg group-hover/logout:rotate-12 transition-transform">🚪</span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <?php if ($success): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?= $success ?>',
                        background: 'rgba(15, 23, 42, 0.95)',
                        color: '#fff',
                        confirmButtonColor: '#2563eb',
                        backdrop: `rgba(15, 23, 42, 0.4) blur(4px)`,
                        customClass: {
                            popup: 'glass border border-white/10 rounded-3xl'
                        }
                    });
                </script>
            <?php endif; ?>

            <?php if ($error): ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: '<?= $error ?>',
                        background: 'rgba(15, 23, 42, 0.95)',
                        color: '#fff',
                        confirmButtonColor: '#2563eb',
                        backdrop: `rgba(15, 23, 42, 0.4) blur(4px)`,
                        customClass: {
                            popup: 'glass border border-white/10 rounded-3xl'
                        }
                    });
                </script>
            <?php endif; ?>

            <!-- Profile Form Card -->
            <div class="glass rounded-[2.5rem] p-8 md:p-12 shadow-2xl border border-white/10">
                <form method="POST" enctype="multipart/form-data" class="space-y-8">
                    <!-- Photo Section -->
                    <div class="flex flex-col md:flex-row items-center gap-8 mb-10">
                        <div class="relative group cursor-pointer">
                            <div class="absolute -inset-1 bg-gradient-to-tr from-cyan-400 to-blue-600 rounded-full opacity-70 blur-md group-hover:opacity-100 transition duration-500"></div>
                            <img src="<?= $photoUrl ?>" class="relative w-36 h-36 rounded-full object-cover border-4 border-white/20 shadow-2xl transition transform group-hover:scale-105 profile-preview">
                            <label class="absolute bottom-1 right-1 bg-blue-600 text-white rounded-full p-2.5 shadow-lg cursor-pointer hover:bg-blue-500 transition border-2 border-white/20">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <input type="file" name="photo" class="hidden" onchange="const preview = document.querySelector('.profile-preview'); preview.src = window.URL.createObjectURL(this.files[0])">
                            </label>
                        </div>
                        <div class="flex-1 text-center md:text-left text-white">
                             <h3 class="text-2xl font-bold mb-1"><?= htmlspecialchars($user['nama']) ?></h3>
                             <p class="text-slate-400 font-medium mb-4"><?= htmlspecialchars($user['email']) ?></p>
                             <div class="bg-blue-600/20 border border-blue-500/30 px-4 py-2 rounded-full inline-block text-blue-300 text-xs font-bold uppercase tracking-wider">
                                Role: <?= $user['role'] ?>
                             </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-300 ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['nama']) ?>" required 
                                   class="w-full px-5 py-4 rounded-2xl bg-white/5 border border-white/10 focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-white transition-all shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-300 ml-1">Email (Akun)</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled 
                                   class="w-full px-5 py-4 rounded-2xl bg-white/5 border border-white/5 text-slate-500 font-medium cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-300 ml-1">NIDN (Nomor Induk Dosen Nasional)</label>
                            <input type="text" value="<?= htmlspecialchars($user['nidn'] ?? '-') ?>" disabled 
                                   class="w-full px-5 py-4 rounded-2xl bg-white/5 border border-white/5 text-slate-500 font-medium cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-300 ml-1">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" value="<?= htmlspecialchars($user['nip'] ?? '-') ?>" disabled 
                                   class="w-full px-5 py-4 rounded-2xl bg-white/5 border border-white/5 text-slate-500 font-medium cursor-not-allowed">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-300 ml-1">Update Password</label>
                        <input type="password" name="password" placeholder="Masukkan password baru jika ingin diganti" 
                               class="w-full px-5 py-4 rounded-2xl bg-white/5 border border-white/10 focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none font-medium text-white transition-all shadow-sm">
                    </div>

                    <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row gap-4">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-8 py-5 rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all transform hover:-translate-y-1 active:scale-[0.98] flex items-center justify-center gap-3 border border-white/10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan Akun
                        </button>
                        <a href="dashboard.php" class="px-12 py-5 rounded-2xl glass text-blue-200 hover:bg-white/10 hover:text-white font-bold transition-all flex items-center justify-center border border-white/10">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
