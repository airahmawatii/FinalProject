<?php
session_start();
require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/UserModel.php"; // Use Model for cleaner logic
require_once "../../../../app/Models/ProdiModel.php"; 

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db = new Database();
$pdo = $db->connect();
$userModel = new UserModel($pdo);
$prodiModel = new ProdiModel($pdo);

// Ambil data prodi untuk dropdown
$prodiList = $prodiModel->getAll();

$error = "";
$success = "";

// Proses tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    // Additional Data
    $data = [
        'nim' => $_POST['nim'] ?? null,
        'nidn' => $_POST['nidn'] ?? null,
        'nip' => $_POST['nip'] ?? null,
        'angkatan_id' => $_POST['angkatan_id'] ?? null,
        'prodi_id' => $_POST['prodi_id'] ?? null,
        'status' => 'active' // Admin yang nambahin langsung Aktif
    ];

    if (empty($error)) {
        try {
            // Use Model to Create
            $userModel->create($nama, $email, $password, $role, $data);
            header("Location: index.php?msg=created");
            exit;
        } catch (Exception $e) {
            $error = "Gagal menambah user: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tambah Pengguna | Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
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
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-6">

    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <div class="w-full max-w-xl glass p-10 rounded-[2.5rem] shadow-2xl relative z-10 border border-white/20 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
        
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Tambah Pengguna</h2>
                <p class="text-blue-300/60 text-sm mt-1">Registrasikan akun baru ke dalam sistem.</p>
            </div>
            <a href="index.php" class="w-10 h-10 rounded-xl glass flex items-center justify-center text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all border border-white/10 group">
                <span class="group-hover:rotate-90 transition-transform duration-300">✕</span>
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 text-red-300 p-4 rounded-2xl mb-8 text-sm border border-red-500/20 flex items-center gap-3">
                <span class="text-xl">⚠️</span> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <input type="text" name="nama" required placeholder="Nama Lengkap" 
                           class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-blue-300/30 border-white/10 transition-all">
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Email Kampus</label>
                    <input type="email" name="email" id="emailInput" required placeholder="nama@ubpkarawang.ac.id" 
                           class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-blue-300/30 border-white/10 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-blue-300/30 border-white/10 transition-all">
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Akses Role</label>
                    <div class="relative">
                        <select name="role" id="roleSelect" required 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <option value="">Pilih Role</option>
                            <option value="admin">Administrator</option>
                            <option value="dosen">Dosen Pengajar</option>
                            <option value="mahasiswa">Mahasiswa</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fields for Mahasiswa -->
            <div id="mahasiswaFields" class="hidden space-y-6 bg-white/5 p-6 rounded-[2rem] border border-white/10 animate-fade-in">
                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Nomor Induk Mahasiswa (NIM)</label>
                    <input type="text" name="nim" id="nimInput" placeholder="24416..." 
                           class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-blue-300/30 border-white/10">
                    <p class="text-[10px] text-slate-500 font-bold ml-1 uppercase">Tahun Angkatan otomatis terdeteksi dari NIM.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Program Studi</label>
                    <div class="relative">
                        <select name="prodi_id" 
                                class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 border-white/10 appearance-none cursor-pointer">
                            <option value="">Pilih Program Studi</option>
                            <?php foreach ($prodiList as $p): ?>
                                <option value="<?= $p['id_prodi'] ?>"><?= htmlspecialchars($p['nama_prodi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fields for Dosen -->
            <div id="dosenFields" class="hidden space-y-6 bg-white/5 p-6 rounded-[2rem] border border-white/10 animate-fade-in">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">NIDN</label>
                        <input type="text" name="nidn" placeholder="Nomor Induk Dosen" 
                               class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-blue-300/30 border-white/10">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">NIP (Optional)</label>
                        <input type="text" name="nip" placeholder="Nomor Induk Pegawai" 
                               class="w-full px-5 py-3.5 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white placeholder-blue-300/30 border-white/10">
                    </div>
                </div>
            </div>

            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] border border-white/10">
                    Simpan Perubahan
                </button>
                <a href="index.php" class="px-8 py-4 rounded-2xl glass text-slate-300 hover:bg-white/20 font-bold transition flex items-center border border-white/10">
                    Batalkan
                </a>
            </div>
        </form>
    </div>

<script>
    const emailInput = document.getElementById('emailInput');
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

    roleSelect.addEventListener('change', updateFields);

    emailInput.addEventListener('input', function() {
        const email = this.value.toLowerCase();
        if (email.includes('@mhs.ubpkarawang.ac.id')) {
            roleSelect.value = 'mahasiswa';
        } else if (email.includes('@ubpkarawang.ac.id')) {
            roleSelect.value = 'dosen';
        }
        updateFields();
    });
</script>

</body>
</html>
