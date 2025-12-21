<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/CourseModel.php';

$db = new Database();
$pdo = $db->connect();

$dosen_id = $_SESSION['user']['id'];

// Get students enrolled in courses taught by this dosen
$sql = "
    SELECT DISTINCT u.nama, m.nim, u.email, a.tahun as angkatan
    FROM users u
    JOIN enrollments e ON u.id = e.student_id
    JOIN courses co ON e.course_id = co.id
    JOIN dosen_courses dc ON dc.matkul_id = co.id
    LEFT JOIN mahasiswa m ON u.id = m.user_id
    LEFT JOIN angkatan a ON m.angkatan_id = a.id_angkatan
    WHERE dc.dosen_id = :dosen_id AND u.role = 'mahasiswa'
    ORDER BY u.nama ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['dosen_id' => $dosen_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex text-white font-outfit">

    <!-- Include Shared Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full md:w-auto min-h-screen transition-all duration-300 md:ml-20">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-3xl font-bold mb-2 text-white">Data Mahasiswa 👥</h1>
                     <p class="text-blue-200">Daftar mahasiswa yang terdaftar di kelas-kelas Anda.</p>
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
                                <a href="profile.php" 
                                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-blue-100 hover:bg-blue-500/20 hover:text-white transition-all font-bold text-xs uppercase tracking-wider group/profile">
                                    <span class="text-lg group-hover/profile:scale-110 transition-transform">👤</span>
                                    Profile
                                </a>
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

            <div class="glass rounded-[2rem] overflow-hidden border border-white/10 shadow-2xl">
                <?php if (empty($students)): ?>
                    <div class="text-center py-20">
                        <div class="text-7xl mb-6 opacity-20">👥</div>
                        <h3 class="text-2xl font-bold text-white opacity-40">Belum ada mahasiswa</h3>
                        <p class="text-blue-200 mt-2 opacity-30">Belum ada mahasiswa yang mengambil mata kuliah Anda.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white/5 text-blue-300 uppercase text-[10px] tracking-[0.2em] font-extrabold border-b border-white/10">
                                    <th class="py-6 px-8">Nama Mahasiswa</th>
                                    <th class="py-6 px-8">NIM</th>
                                    <th class="py-6 px-8">Email</th>
                                    <th class="py-6 px-8 text-center">Angkatan</th>
                                </tr>
                            </thead>
                            <tbody class="text-blue-100 text-sm font-medium">
                                <?php foreach ($students as $s): ?>
                                <tr class="border-b border-white/[0.03] hover:bg-white/[0.05] transition-all group">
                                    <td class="py-5 px-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 border border-blue-500/20 flex items-center justify-center text-blue-400 font-bold group-hover:scale-110 transition-transform shadow-inner">
                                                <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                            </div>
                                            <span class="font-bold text-white group-hover:text-blue-300 transition-colors text-base"><?= htmlspecialchars($s['nama']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-5 px-8 text-slate-400 font-mono text-xs"><?= htmlspecialchars($s['nim'] ?? '-') ?></td>
                                    <td class="py-5 px-8 text-slate-400 lowercase"><?= htmlspecialchars($s['email']) ?></td>
                                    <td class="py-5 px-8 text-center">
                                        <span class="bg-blue-500/10 text-blue-300 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-500/20">
                                            <?= htmlspecialchars($s['angkatan'] ?? '-') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($students)): ?>
            <div class="mt-8 flex justify-end">
                <div class="bg-blue-500/10 border border-blue-500/20 px-6 py-3 rounded-2xl">
                    <span class="text-[10px] text-blue-300 uppercase font-extrabold tracking-widest mr-3 opacity-60">Total Mahasiswa:</span>
                    <span class="text-2xl font-black text-white"><?= count($students) ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
