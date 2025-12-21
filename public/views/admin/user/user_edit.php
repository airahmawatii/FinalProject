<?php
session_start();
require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/UserModel.php";
require_once "../../../../app/Models/ProdiModel.php";

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db = new Database();
$pdo = $db->connect();
$userModel = new UserModel($pdo);
$prodiModel = new ProdiModel($pdo);

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID user tidak ditemukan.");
}

// Ambil data user lengkap (termasuk NIM/NIDN + Prodi)
$currentUser = $userModel->findById($id);
// Ambil list prodi
$prodiList = $prodiModel->getAll();

if (!$currentUser) {
    die("User tidak ditemukan.");
}

$error = "";

// Proses edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'] ?? 'active';

    $data = [
        'nim' => $_POST['nim'] ?? null,
        'nidn' => $_POST['nidn'] ?? null,
        'nip' => $_POST['nip'] ?? null,
        'prodi_id' => $_POST['prodi_id'] ?? null,
        'status' => $_POST['status'] ?? 'active'
    ];

    try {
        // Update user (Transaction handled inside Model)
        $userModel->update($id, $nama, $email, $role, $data);
        header("Location: index.php?msg=updated");
        exit;
    } catch (Exception $e) {
        $error = "Update Gagal: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Pengguna | TaskAcademia</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen p-8 flex items-center justify-center font-outfit">

    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <div class="w-full max-w-xl glass p-10 rounded-[2.5rem] shadow-2xl relative z-10 border border-white/20 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500"></div>

        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Edit Pengguna</h2>
                <p class="text-blue-300/60 text-sm mt-1">Perbarui informasi kredensial dan profil user.</p>
            </div>
            <a href="index.php" class="w-10 h-10 rounded-xl glass flex items-center justify-center text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all border border-white/10 group">
                <span class="group-hover:rotate-90 transition-transform duration-300">✕</span>
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 text-red-300 p-4 rounded-2xl mb-8 text-sm border border-red-500/20 flex items-center gap-3 font-bold">
                <span class="text-xl">⚠️</span> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Nama Lengkap</label>
                <input type="text" name="nama" required placeholder="Nama User" 
                       value="<?= htmlspecialchars($currentUser['nama']) ?>" 
                       class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 transition-all">
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Alamat Email</label>
                <input type="email" name="email" required placeholder="Email" 
                       value="<?= htmlspecialchars($currentUser['email']) ?>" 
                       class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Hak Akses</label>
                    <div class="relative">
                        <select name="role" id="roleSelect" required 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <option value="admin" <?= $currentUser['role']=='admin'?'selected':'' ?>>Administrator</option>
                            <option value="dosen" <?= $currentUser['role']=='dosen'?'selected':'' ?>>Dosen Pengajar</option>
                            <option value="mahasiswa" <?= $currentUser['role']=='mahasiswa'?'selected':'' ?>>Mahasiswa</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Status Keaktifan</label>
                    <div class="relative">
                        <select name="status" required 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <option value="active" <?= ($currentUser['status'] ?? 'active') =='active'?'selected':'' ?>>Active / Terverifikasi</option>
                            <option value="pending" <?= ($currentUser['status'] ?? 'active') =='pending'?'selected':'' ?>>Pending / Suspended</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Fields -->
            <div id="mahasiswaFields" class="hidden space-y-6 bg-white/5 p-6 rounded-[2rem] border border-white/10 animate-fade-in">
                 <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Nomor Induk Mahasiswa (NIM)</label>
                    <input type="text" name="nim" value="<?= htmlspecialchars($currentUser['nim'] ?? '') ?>"
                           class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10">
                </div>
                
                 <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Program Studi</label>
                    <div class="relative">
                        <select name="prodi_id" 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <option value="">-- Pilih Prodi --</option>
                            <?php foreach ($prodiList as $p): ?>
                                <option value="<?= $p['id_prodi'] ?>" <?= ($currentUser['prodi_id'] ?? '') == $p['id_prodi'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_prodi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div id="dosenFields" class="hidden space-y-6 bg-white/5 p-6 rounded-[2rem] border border-white/10 animate-fade-in">
                 <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">NIDN</label>
                    <input type="text" name="nidn" value="<?= htmlspecialchars($currentUser['nidn'] ?? '') ?>"
                           class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10">
                </div>
                 <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">NIP (Optional)</label>
                    <input type="text" name="nip" value="<?= htmlspecialchars($currentUser['nip'] ?? '') ?>"
                           class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10">
                </div>
            </div>

            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] border border-white/10">
                    Update Perubahan
                </button>
                <a href="index.php" class="px-8 py-4 rounded-2xl glass text-slate-300 hover:bg-white/20 font-bold transition flex items-center border border-white/10">
                    Batalkan
                </a>
            </div>
        </form>
    </div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const mahasiswaFields = document.getElementById('mahasiswaFields');
    const dosenFields = document.getElementById('dosenFields');

    function updateFields() {
        const role = roleSelect.value;
        if (role === 'mahasiswa') {
            mahasiswaFields.classList.remove('hidden');
            dosenFields.classList.add('hidden');
        } else if (role === 'dosen') {
            dosenFields.classList.remove('hidden');
            mahasiswaFields.classList.add('hidden');
        } else {
            mahasiswaFields.classList.add('hidden');
            dosenFields.classList.add('hidden');
        }
    }

    // Initialize on load
    updateFields();
    
    // Listen for changes
    roleSelect.addEventListener('change', updateFields);
</script>

</body>
</html>
