<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../app/config/database.php";
require_once "../../../app/Models/EnrollmentModel.php";

$db = new Database();
$pdo = $db->connect();
$enrollModel = new EnrollmentModel($pdo);

// Fetch students and classes for dropdowns
$students = $pdo->query("SELECT id, nama FROM users WHERE role='mahasiswa' ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT id_kelas, nama_kelas FROM class ORDER BY nama_kelas")->fetchAll(PDO::FETCH_ASSOC);

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        // Handle Delete
        if ($enrollModel->delete($_POST['delete_id'])) {
            $msg = "Peserta berhasil dihapus dari kelas.";
        }
    } else {
        // Handle Enroll
        if ($enrollModel->enroll($_POST['student_id'], $_POST['class_id'])) {
            $msg = "Mahasiswa berhasil dimasukkan ke kelas.";
        } else {
            $error = "Mahasiswa sudah terdaftar di kelas ini atau terjadi kesalahan.";
        }
    }
}

$enrollments = $enrollModel->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Peserta Kelas | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex font-outfit text-white">
    <?php include __DIR__ . '/../layouts/sidebar_admin.php'; ?>
   <main class="flex-1 min-h-screen relative">
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[100px]"></div>
        </div>
        <div class="p-6 md:p-10 max-w-7xl mx-auto pt-20 md:pt-10 relative z-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Peserta Kelas</h1>
                     <p class="text-blue-200">Kelola dan masukkan mahasiswa ke dalam kelas yang tersedia.</p>
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
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['nama']) ?>&background=2563eb&color=fff&bold=true" 
                                     alt="Profile" class="w-full h-full rounded-full object-cover border-2 border-white/20">
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
                                <a href="../../logout.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-100 hover:bg-red-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/logout">
                                    <span class="text-lg group-hover/logout:rotate-12 transition-transform">🚪</span>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <?php if ($msg): ?>
                <div class="glass border-green-500/30 text-green-300 p-4 rounded-xl mb-6"><?= $msg ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="glass border-red-500/30 text-red-300 p-4 rounded-xl mb-6"><?= $error ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                <!-- Form Card -->
                <div class="lg:col-span-1">
                    <div class="glass p-8 rounded-[2rem] border border-white/20 h-fit sticky top-10 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-all"></div>
                        
                        <h3 class="text-xl font-bold mb-8 flex items-center gap-3 text-white">
                            <span class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400">➕</span>
                            Assign Mahasiswa
                        </h3>
                        
                        <form method="POST" class="space-y-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Pilih Mahasiswa</label>
                                <div class="relative group/input">
                                    <select name="student_id" required class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/50 border-white/10 appearance-none cursor-pointer">
                                        <option value="">-- Mahasiswa --</option>
                                        <?php foreach ($students as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within/input:text-blue-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Pilih Kelas</label>
                                <div class="relative group/input">
                                    <select name="class_id" required class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/50 border-white/10 appearance-none cursor-pointer">
                                        <option value="">-- Kelas --</option>
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= $c['id_kelas'] ?>"><?= htmlspecialchars($c['nama_kelas']) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within/input:text-blue-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] border border-white/10 mt-4">
                                Masukkan ke Kelas
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="lg:col-span-2">
                    <div class="glass rounded-[2rem] overflow-hidden border border-white/20 shadow-2xl relative">
                        <div class="p-8 border-b border-white/10 bg-white/5 flex justify-between items-center">
                            <h3 class="text-xl font-bold flex items-center gap-3 text-white">
                                <span class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">📋</span>
                                Peserta Terdaftar
                            </h3>
                            <span class="px-4 py-1.5 glass rounded-full text-[10px] font-extrabold text-blue-300 uppercase tracking-widest border border-white/10">
                                <?= count($enrollments) ?> Total
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-white/5 text-blue-300 uppercase text-[10px] font-extrabold tracking-widest opacity-80">
                                    <tr>
                                        <th class="p-6">Identitas Kelas</th>
                                        <th class="p-6">Detail Mahasiswa</th>
                                        <th class="p-6 text-right">Manajemen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php if (!empty($enrollments)): ?>
                                        <?php foreach ($enrollments as $row): ?>
                                        <tr class="hover:bg-white/[0.03] transition-colors group">
                                            <td class="p-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-xl group-hover:scale-110 transition">🎓</div>
                                                    <div class="font-bold text-white text-base"><?= htmlspecialchars($row['nama_kelas']) ?></div>
                                                </div>
                                            </td>
                                            <td class="p-6">
                                                <div class="font-medium text-slate-300 group-hover:text-blue-400 transition"><?= htmlspecialchars($row['student_name']) ?></div>
                                                <div class="text-[10px] text-slate-500 uppercase font-bold tracking-tighter mt-0.5">Active Enrollment</div>
                                            </td>
                                            <td class="p-6 text-right">
                                                <form method="POST" onsubmit="return confirm('Hapus mahasiswa ini dari kelas?')" class="flex justify-end pr-2">
                                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                                    <button class="w-10 h-10 rounded-xl glass bg-red-500/10 hover:bg-red-500/20 text-red-300 flex items-center justify-center transition border border-red-500/30 opacity-0 group-hover:opacity-100 shadow-lg" title="Hapus dari kelas">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="p-20 text-center text-slate-500 italic">
                                                <div class="text-4xl mb-4">🫙</div>
                                                <p class="font-medium">Belum ada peserta yang didaftarkan ke kelas.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
