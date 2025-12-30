<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../../../app/config/config.php";
require_once "../../../../app/config/database.php";

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

$db = new Database();
$pdo = $db->connect();

// Hapus user (We should ideally use Model, but keep it simple for now as per existing style)
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    try {
        $pdo->beginTransaction();

        // 1. Bersihkan tabel-tabel anak (Child Tables)
        // Jika database tidak menggunakan ON DELETE CASCADE, kita harus hapus manual.
        
        // A. Data terkait Mahasiswa
        $pdo->prepare("DELETE FROM task_completions WHERE user_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM submissions WHERE student_id=?")->execute([$id]); 
        $pdo->prepare("DELETE FROM class_students WHERE student_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM enrollments WHERE student_id=?")->execute([$id]);

        // B. Data terkait Dosen
        // Hapus relasi dosen ke matkul
        $pdo->prepare("DELETE FROM dosen_courses WHERE dosen_id=?")->execute([$id]);
        // Hapus tugas yang dibuat dosen ini (akan cascade ke submissions tugas tsb jika DB support, jika tidak kita biarkan DB error atau handle nanti)
        // Kita asumsikan Tasks aman dihapus atau DB akan block jika ada submissions penting.
        // Untuk amannya kita hapus tasks milik dosen ini:
        $pdo->prepare("DELETE FROM tasks WHERE dosen_id=?")->execute([$id]);

        // C. Data Profil Utama
        $pdo->prepare("DELETE FROM mahasiswa WHERE user_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM dosen WHERE user_id=?")->execute([$id]);

        // 2. Hapus user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            header("Location: index.php?msg=deleted");
        } else {
            $pdo->rollBack();
            header("Location: index.php?msg=warning");
        }
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error deleting user: " . $e->getMessage());
        header("Location: index.php?msg=error&detail=" . urlencode("Gagal menghapus: pastikan tidak ada data terkait (Tugas/Pengumpulan) yang mencegah penghapusan."));
        exit;
    }
}

// Ambil semua user dengan JOIN info detail
$users = [];
try {
    $sql = "
        SELECT u.*, 
               m.nim, 
               d.nidn, d.nip,
               a.tahun as angkatan
        FROM users u
        LEFT JOIN mahasiswa m ON u.id = m.user_id
        LEFT JOIN dosen d ON u.id = d.user_id
        LEFT JOIN angkatan a ON m.angkatan_id = a.id_angkatan
        ORDER BY u.created_at DESC
    ";
    $users = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}

// Success message
$msg = "";
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created': $msg = "Pengguna berhasil ditambahkan!"; break;
        case 'updated': $msg = "Data pengguna berhasil diperbarui!"; break;
        case 'deleted': $msg = "Pengguna berhasil dihapus!"; break;
        case 'error': $msg = "Terjadi kesalahan: " . ($_GET['detail'] ?? 'Gagal menghapus pengguna.'); break;
        case 'warning': $msg = "Pengguna tidak ditemukan atau sudah terhapus."; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengguna | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex font-outfit text-white">

    <?php include __DIR__ . '/../../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 min-h-screen relative">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[100px]"></div>
        </div>

        <div class="p-6 md:p-10 max-w-7xl mx-auto pt-20 md:pt-10 relative z-10">
            <!-- Header -->
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                     <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Kelola Pengguna</h1>
                     <p class="text-blue-200">Manajemen lengkap akun Dosen, Mahasiswa, dan Administrator.</p>
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
                                <a href="../../../logout.php" 
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
                <div class="glass border-<?= (strpos($_GET['msg']??'', 'error')!==false || strpos($_GET['msg']??'', 'warning')!==false)?'red':'green' ?>-500/30 text-<?= (strpos($_GET['msg']??'', 'error')!==false || strpos($_GET['msg']??'', 'warning')!==false)?'red':'green' ?>-300 p-4 rounded-xl mb-6"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <!-- Action Bar -->
            <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4 mb-8">
                <div class="flex-1 max-w-2xl group">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400 group-focus-within:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari nama, email, atau NIM/NIP..." 
                               class="w-full pl-12 pr-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-slate-400 transition-all border-white/10 group-focus-within:border-blue-500/50">
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="export_users.php" class="glass px-6 py-4 rounded-2xl text-sm font-bold text-white hover:bg-white/20 transition flex items-center gap-2 border border-white/10">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export CSV
                    </a>
                    
                    <a href="user_add.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-2xl text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:scale-105 transition flex items-center gap-2 border border-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Pengguna
                    </a>
                </div>
            </div>

            <!-- Table Container -->
            <div class="glass rounded-[2rem] overflow-hidden border border-white/20 shadow-2xl mb-10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gradient-to-r from-white/5 to-transparent border-b border-white/10">
                            <tr>
                                <th class="p-6 text-blue-300 font-bold uppercase tracking-wider text-[10px]">Nama / Email</th>
                                <th class="p-6 text-blue-300 font-bold uppercase tracking-wider text-[10px]">Role & Identifier</th>
                                <th class="p-6 text-blue-300 font-bold uppercase tracking-wider text-[10px]">Account Status</th>
                                <th class="p-6 text-right text-blue-300 font-bold uppercase tracking-wider text-[10px]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                <tr class="hover:bg-white/[0.03] transition-colors group">
                                    <td class="p-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500/20 to-indigo-600/20 flex items-center justify-center text-blue-300 font-bold border border-white/10 group-hover:scale-110 transition">
                                                <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-white text-base mb-0.5"><?= htmlspecialchars($u['nama']) ?></div>
                                                <div class="text-xs text-slate-400"><?= htmlspecialchars($u['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase mb-2 inline-block tracking-widest
                                            <?= $u['role']=='admin'?'bg-purple-500/20 text-purple-300 border border-purple-500/30':'' ?>
                                            <?= $u['role']=='dosen'?'bg-blue-500/20 text-blue-300 border border-blue-500/30':'' ?>
                                            <?= $u['role']=='mahasiswa'?'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30':'' ?>">
                                            <?= htmlspecialchars($u['role']) ?>
                                        </span>
                                        <?php if ($u['role'] === 'mahasiswa' && $u['nim']): ?>
                                            <div class="flex flex-col gap-0.5">
                                                <div class="text-xs font-bold text-slate-300">NIM: <?= htmlspecialchars($u['nim']) ?></div>
                                                <div class="text-[10px] text-slate-500 uppercase font-bold tracking-tight">Angkatan <?= htmlspecialchars($u['angkatan'] ?? '-') ?></div>
                                            </div>
                                        <?php elseif ($u['role'] === 'dosen'): ?>
                                            <div class="flex flex-col gap-0.5">
                                                <?php if ($u['nidn']): ?><div class="text-xs font-bold text-slate-300">NIDN: <?= htmlspecialchars($u['nidn']) ?></div><?php endif; ?>
                                                <?php if ($u['nip']): ?><div class="text-[10px] text-slate-500 uppercase font-bold tracking-tight">NIP: <?= htmlspecialchars($u['nip']) ?></div><?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-6">
                                        <?php if (isset($u['status']) && $u['status'] === 'pending'): ?>
                                            <span class="flex items-center gap-2 text-yellow-300 bg-yellow-500/10 px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest w-fit border border-yellow-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span> Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="flex items-center gap-2 text-emerald-300 bg-emerald-500/10 px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-widest w-fit border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-6 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                            <a href="user_edit.php?id=<?= $u['id'] ?>"
                                                class="glass bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 p-2.5 rounded-xl transition border border-blue-500/30 shadow-lg"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </a>
                                            <a href="index.php?delete_id=<?= $u['id'] ?>"
                                                onclick="return confirm('Yakin hapus user ini?')"
                                                class="glass bg-red-500/10 hover:bg-red-500/20 text-red-300 p-2.5 rounded-xl transition border border-red-500/30 shadow-lg"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="p-20 text-center text-slate-500 italic">
                                        <div class="text-4xl mb-4">🌫️</div>
                                        <p class="font-medium">Belum ada data pengguna ditemukan.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

<script>
// Live Search
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// CSV Import
const csvInput = document.getElementById('csvFile');
csvInput.addEventListener('change', function() {
    if (this.files.length === 0) return;

    const formData = new FormData();
    formData.append('csv_file', this.files[0]);

    Swal.fire({
        title: 'Mengupload...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('../../api/import_students.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Import Berhasil!',
                text: data.message,
                confirmButtonColor: '#3b82f6'
            }).then(() => location.reload());
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Import',
                text: data.message,
                confirmButtonColor: '#3b82f6'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal menghubungi server',
            confirmButtonColor: '#3b82f6'
        });
    });
    
    this.value = '';
});
</script>

</body>
</html>
