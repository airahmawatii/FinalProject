<?php
session_start();
require_once "../../../../app/config/config.php";
require_once "../../../../app/config/database.php";

// Cek role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db = new Database();
$pdo = $db->connect();

// Hapus user
// Hapus user
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    try {
        $pdo->beginTransaction();
        
        // 1. Delete from dosen_courses (if exists)
        $pdo->prepare("DELETE FROM dosen_courses WHERE dosen_id=?")->execute([$id]);
        
        // 2. Delete from tasks (assignments created by this dosen)
        $pdo->prepare("DELETE FROM tasks WHERE dosen_id=?")->execute([$id]);

        // 3. Delete from enrollments & class_students (if student)
        $pdo->prepare("DELETE FROM enrollments WHERE student_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM class_students WHERE student_id=?")->execute([$id]);
        
        // 4. Finally delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        header("Location: index.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Gagal menghapus user. Error: " . $e->getMessage());
    }
}

// Ambil semua user
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Success message
$msg = "";
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created': $msg = "Pengguna berhasil ditambahkan!"; break;
        case 'updated': $msg = "Data pengguna berhasil diperbarui!"; break;
        case 'deleted': $msg = "Pengguna berhasil dihapus!"; break;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen text-gray-800 p-8">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <a href="../dashboard_admin.php" class="bg-indigo-100 hover:bg-indigo-200 p-2 rounded-xl transition">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold">Kelola Pengguna</h1>
                    <p class="text-blue-200 text-sm">Manajemen akun Dosen, Mahasiswa, dan Admin.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="document.getElementById('csvFile').click()" class="bg-green-600 hover:bg-green-500 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                    <span>📂</span> Import Excel/CSV
                </button>
                <input type="file" id="csvFile" accept=".csv" class="hidden">
                
                <a href="user_add.php" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                    <span>+</span> Tambah Pengguna
                </a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-xl mb-6"><?= $msg ?></div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="mb-6">
            <input type="text" id="searchInput" placeholder="🔍 Cari nama atau email..." 
                   class="w-full px-6 py-3 bg-white border-2 border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none text-gray-800 placeholder-gray-400 transition">
        </div>

        <!-- Table Container -->
        <div class="glass rounded-3xl overflow-hidden text-gray-800 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider font-semibold border-b">
                        <tr>
                            <th class="p-5">Nama / Email</th>
                            <th class="p-5">Role</th>
                            <th class="p-5">Status</th>
                            <th class="p-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-blue-50/50 transition duration-150">
                                <td class="p-5">
                                    <div class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($u['nama']) ?></div>
                                    <div class="text-sm text-blue-500"><?= htmlspecialchars($u['email']) ?></div>
                                </td>
                                <td class="p-5">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                                        <?= $u['role']=='admin'?'bg-purple-100 text-purple-700':'' ?>
                                        <?= $u['role']=='dosen'?'bg-blue-100 text-blue-700':'' ?>
                                        <?= $u['role']=='mahasiswa'?'bg-teal-100 text-teal-700':'' ?>">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td class="p-5">
                                    <?php if (isset($u['status']) && $u['status'] === 'pending'): ?>
                                        <span class="flex items-center gap-2 text-yellow-600 bg-yellow-100 px-3 py-1 rounded-full text-xs font-bold w-fit">
                                            <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="flex items-center gap-2 text-green-600 bg-green-100 px-3 py-1 rounded-full text-xs font-bold w-fit">
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5 text-right flex gap-3 justify-end items-center">
                                    <!-- <a href="user_edit.php?id=<?= $u['id'] ?>" class="text-gray-400 hover:text-blue-600 transition font-medium">Edit</a> -->
                                    <a href="index.php?delete_id=<?= $u['id'] ?>"
                                        onclick="return confirm('Yakin hapus user ini?')"
                                        class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-2 rounded-lg text-sm font-semibold transition">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-10 text-center text-gray-500 italic">Belum ada data pengguna.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
// Live Search Functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const nama = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
        const email = row.querySelector('td:first-child .text-sm')?.textContent.toLowerCase() || '';
        
        if (nama.includes(searchValue) || email.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// CSV Import Logic
const csvInput = document.getElementById('csvFile');
csvInput.addEventListener('change', function() {
    if (this.files.length === 0) return;

    const formData = new FormData();
    formData.append('csv_file', this.files[0]);

    Swal.fire({
        title: 'Mengupload...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('../../api/import_students.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            let details = '';
            if (data.details && data.details.length > 0) {
                details = '<br><div class="text-left text-xs bg-gray-100 p-2 rounded mt-2 max-h-40 overflow-y-auto">' + data.details.join('<br>') + '</div>';
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Import Selesai!',
                html: data.message + details,
                confirmButtonText: 'Oke'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Import',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: 'Gagal menghubungi server.'
        });
    });
    
    // Reset input
    this.value = '';
});
</script>

</body>
</html>
