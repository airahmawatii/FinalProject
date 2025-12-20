<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') die("Akses ditolak");

require_once "../../../../app/config/database.php";
require_once "../../../../app/Models/CourseModel.php";
require_once "../../../../app/Models/EnrollmentModel.php";

$db = new Database();
$pdo = $db->connect();
$model = new CourseModel($pdo);

$enrollModel = new EnrollmentModel($pdo);

$id = $_GET['id'] ?? null;
if (!$id) die("ID tidak ditemukan");

// data MK
$data = $model->find($id);
if (!$data) die("Mata Kuliah tidak ditemukan");

// ambil daftar dosen
$users = $pdo->query("SELECT id, nama FROM users WHERE role='dosen'")->fetchAll(PDO::FETCH_ASSOC);

// ambil dosen pengajar dari tabel pivot dengan detail user
$stmt_dosen = $pdo->prepare("
    SELECT u.id, u.nama, u.email 
    FROM users u 
    JOIN dosen_courses dc ON u.id = dc.dosen_id 
    WHERE dc.matkul_id = ?
");
$stmt_dosen->execute([$id]);
$assigned_lecturers = $stmt_dosen->fetchAll(PDO::FETCH_ASSOC);
$current_dosen_ids = array_column($assigned_lecturers, 'id');

// Get enrolled students
$enrolled_students = $enrollModel->getStudentsByCourse($id);
// Get all students for dropdown
$all_students = $pdo->query("SELECT id, nama FROM users WHERE role='mahasiswa' ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
// Get all classes for dropdown
$all_classes = $pdo->query("SELECT id_kelas, nama_kelas FROM class ORDER BY nama_kelas")->fetchAll(PDO::FETCH_ASSOC);

$error = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Logic Mahasiswa ---
    if (isset($_POST['enroll_student'])) {
        if ($enrollModel->enrollStudentToCourse($_POST['student_id'], $id)) {
            $success_msg = "Mahasiswa berhasil ditambahkan.";
            $enrolled_students = $enrollModel->getStudentsByCourse($id);
        } else {
            $error = "Gagal menambahkan mahasiswa atau sudah terdaftar.";
        }
    } elseif (isset($_POST['enroll_class'])) {
        $count = $enrollModel->enrollClassToCourse($_POST['class_id'], $id);
        if ($count > 0) {
            $success_msg = "Berhasil menambahkan $count mahasiswa dari kelas terpilih.";
            $enrolled_students = $enrollModel->getStudentsByCourse($id);
        } else {
            $success_msg = "Semua mahasiswa di kelas ini sudah terdaftar.";
        }
    } elseif (isset($_POST['unenroll_student'])) {
        if ($enrollModel->unenrollStudentFromCourse($_POST['student_id'], $id)) {
            $success_msg = "Mahasiswa berhasil dihapus dari kursus.";
            $enrolled_students = $enrollModel->getStudentsByCourse($id);
        } else {
            $error = "Gagal menghapus mahasiswa.";
        }
    
    // --- Logic Dosen ---
    } elseif (isset($_POST['add_dosen'])) {
        $did = $_POST['dosen_id'];
        // Cek duplicate
        if (in_array($did, $current_dosen_ids)) {
            $error = "Dosen tersebut sudah terdaftar di mata kuliah ini.";
        } else {
            try {
                $ins = $pdo->prepare("INSERT INTO dosen_courses (dosen_id, matkul_id) VALUES (?, ?)");
                $ins->execute([$did, $id]);
                $success_msg = "Dosen berhasil ditambahkan.";
                // Refresh list
                $stmt_dosen->execute([$id]);
                $assigned_lecturers = $stmt_dosen->fetchAll(PDO::FETCH_ASSOC);
                $current_dosen_ids = array_column($assigned_lecturers, 'id');
            } catch (Exception $e) {
                $error = "Gagal menambahkan dosen: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['remove_dosen'])) {
        try {
            $del = $pdo->prepare("DELETE FROM dosen_courses WHERE dosen_id=? AND matkul_id=?");
            $del->execute([$_POST['dosen_id'], $id]);
            $success_msg = "Dosen berhasil dihapus.";
            // Refresh list
            $stmt_dosen->execute([$id]);
            $assigned_lecturers = $stmt_dosen->fetchAll(PDO::FETCH_ASSOC);
            $current_dosen_ids = array_column($assigned_lecturers, 'id');
        } catch (Exception $e) {
            $error = "Gagal menghapus dosen: " . $e->getMessage();
        }

    // --- Logic Update Info MK ---
    } elseif (isset($_POST['update_course'])) {
        if ($model->update($id, $_POST['name'], $_POST['semester'])) {
            $success_msg = "Informasi mata kuliah berhasil diupdate.";
            $data = $model->find($id);
        } else {
            $error = "Gagal mengupdate mata kuliah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Mata Kuliah | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
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
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-4 relative">

    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-[10%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
        <div class="absolute bottom-[10%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <!-- Main Card -->
    <div class="w-full max-w-4xl glass p-8 rounded-3xl shadow-2xl relative z-10 my-10">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Mata Kuliah</h2>
            <a href="index.php" class="text-gray-800 hover:text-red-500 transition text-2xl font-bold">×</a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-6 text-sm flex items-center gap-2">
                <span>⚠️</span> <?= $error ?>
            </div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-xl mb-6 text-sm flex items-center gap-2">
                <span>✅</span> <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Left Column: Course Info -->
            <div class="space-y-6">
                <h3 class="font-bold text-gray-700 border-b pb-2">Informasi Umum</h3>
                
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="update_course" value="1">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Mata Kuliah</label>
                        <input type="text" name="name" required placeholder="Nama Mata Kuliah" 
                               value="<?= htmlspecialchars($data['name']) ?>" 
                               class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition font-semibold text-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Semester</label>
                        <select name="semester" required 
                                class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition font-semibold text-gray-700">
                            <?php for ($i=1; $i<=8; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == $data['semester'] ? 'selected' : '' ?>>
                                    Semester <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>


                    <div class="pt-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition transform hover:-translate-y-1">
                            💾 Simpan Perubahan
                        </button>
                    </div>
                </form>

                <!-- Dosen Assignment Section -->
                <div class="pt-6 border-t">
                    <h3 class="font-bold text-gray-700 border-b pb-2 flex items-center gap-2 mb-4">
                        <span>👨‍🏫</span> Kelola Dosen Pengampu
                    </h3>
                    
                    <!-- Form Tambah Dosen -->
                    <form method="POST" class="flex gap-2 mb-4">
                        <input type="hidden" name="add_dosen" value="1">
                        <div class="flex-1">
                            <select name="dosen_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition text-sm">
                                <option value="">-- Tambah Dosen --</option>
                                <?php foreach ($users as $u): ?>
                                    <?php if (!in_array($u['id'], $current_dosen_ids)): ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl font-bold shadow-lg transition">
                            +
                        </button>
                    </form>

                    <!-- List Dosen -->
                    <div class="bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($assigned_lecturers)): ?>
                                    <tr>
                                        <td class="p-4 text-center text-gray-500 italic text-sm">Belum ada dosen pengampu.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($assigned_lecturers as $d): ?>
                                        <tr class="hover:bg-white transition bg-white/50">
                                            <td class="p-3 pl-4 font-semibold text-gray-700 text-sm">
                                                <?= htmlspecialchars($d['nama']) ?>
                                                <div class="text-[10px] text-gray-400 font-normal"><?= htmlspecialchars($d['email']) ?></div>
                                            </td>
                                            <td class="p-3 text-right">
                                                <form method="POST" onsubmit="return confirm('Hapus dosen ini dari mata kuliah?')">
                                                    <input type="hidden" name="remove_dosen" value="1">
                                                    <input type="hidden" name="dosen_id" value="<?= $d['id'] ?>">
                                                    <button class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Enrollment -->
            <div class="space-y-6">
                <h3 class="font-bold text-gray-700 border-b pb-2 flex items-center gap-2">
                    <span>👥</span> Kelola Mahasiswa
                </h3>

                <!-- Add Student Form -->
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="enroll_student" value="1">
                    <div class="flex-1">
                        <select name="student_id" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition text-sm">
                            <option value="">-- Tambah Mahasiswa --</option>
                            <?php foreach ($all_students as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-xl font-bold shadow-lg transition">
                        +
                    </button>
                </form>

                <!-- Bulk Enrollment Form -->
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="enroll_class" value="1">
                    <div class="flex-1">
                        <select name="class_id" required class="w-full px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition text-sm">
                            <option value="">-- Tambah Sekelas --</option>
                            <?php foreach ($all_classes as $c): ?>
                                <option value="<?= $c['id_kelas'] ?>"><?= htmlspecialchars($c['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-xl font-bold shadow-lg transition">
                        +
                    </button>
                </form>

                <!-- Student List -->
                <div class="bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden h-[400px] overflow-y-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 text-gray-600 text-xs uppercase font-bold border-b border-gray-200 sticky top-0">
                            <tr>
                                <th class="p-4">Nama Mahasiswa</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($enrolled_students)): ?>
                                <tr>
                                    <td colspan="2" class="p-6 text-center text-gray-500 italic">Belum ada mahasiswa yang terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($enrolled_students as $st): ?>
                                    <tr class="hover:bg-white transition bg-white/50">
                                        <td class="p-3 pl-4 font-semibold text-gray-700 text-sm">
                                            <?= htmlspecialchars($st['name']) ?>
                                            <div class="text-[10px] text-gray-400 font-normal"><?= htmlspecialchars($st['email']) ?></div>
                                        </td>
                                        <td class="p-3 text-right">
                                            <form method="POST" onsubmit="return confirm('Hapus mahasiswa ini from kelas?')">
                                                <input type="hidden" name="unenroll_student" value="1">
                                                <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                                <button class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
