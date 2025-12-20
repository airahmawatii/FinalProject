<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/CourseModel.php';
require_once __DIR__ . '/../../../app/Models/EnrollmentModel.php';

$db = new Database();
$pdo = $db->connect();
$courseModel = new CourseModel($pdo);
$enrollModel = new EnrollmentModel($pdo);

$courses = $courseModel->getByDosen($_SESSION['user']['id']);
$selected_course = $_GET['course_id'] ?? ($courses[0]['id'] ?? null);

// Verify Dosen owns this course
$is_owner = false;
foreach($courses as $c) { if($c['id'] == $selected_course) $is_owner = true; }

if ($selected_course && !$is_owner) {
    die("Akses ditolak: Anda tidak mengajar mata kuliah ini.");
}

$success_msg = "";
$error_msg = "";

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selected_course) {
    if (isset($_POST['enroll_student'])) {
        if ($enrollModel->enrollStudentToCourse($_POST['student_id'], $selected_course)) {
            $success_msg = "Mahasiswa berhasil ditambahkan.";
        } else {
            $error_msg = "Gagal menambahkan (Mungkin sudah terdaftar).";
        }
    } elseif (isset($_POST['enroll_class'])) {
        $count = $enrollModel->enrollClassToCourse($_POST['class_id'], $selected_course);
        if ($count > 0) $success_msg = "Berhasil menambahkan $count mahasiswa.";
        else $success_msg = "Semua mahasiswa dikelas ini sudah terdaftar.";
    } elseif (isset($_POST['unenroll_student'])) {
        if ($enrollModel->unenrollStudentFromCourse($_POST['student_id'], $selected_course)) {
            $success_msg = "Mahasiswa berhasil dihapus.";
        } else {
            $error_msg = "Gagal menghapus data.";
        }
    }
}

$students = $selected_course ? $enrollModel->getStudentsByCourse($selected_course) : [];

// Data for Dropdowns
$all_students = $pdo->query("SELECT id, nama FROM users WHERE role='mahasiswa' ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
$all_classes = $pdo->query("SELECT id_kelas, nama_kelas FROM class ORDER BY nama_kelas")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lihat Mahasiswa | Dosen</title>
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
        .sidebar { background: rgba(15, 23, 42, 0.95); }
    </style>
</head>
<<<<<<< HEAD
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-6 text-gray-800">

    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none z-0">
         <div class="absolute top-[20%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
         <div class="absolute bottom-[20%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <div class="w-full max-w-7xl glass rounded-3xl p-8 md:p-10 shadow-2xl relative z-10 my-10">
        
        <!-- Header with Back Button (Same as prodi_edit.php) -->
        <div class="mb-8">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-blue-200 hover:text-white mb-2 transition text-sm font-semibold group">
                 <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                 Kembali
            </a>
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-white">Daftar Mahasiswa</h1>
            </div>
            <p class="text-blue-200 text-sm mt-1">Lihat mahasiswa yang terdaftar di kelas Anda.</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="mb-8 flex flex-col md:flex-row items-start md:items-center gap-4 bg-white/50 p-6 rounded-2xl border border-white/40">
            <label class="font-bold text-gray-800 whitespace-nowrap">Pilih Kelas:</label>
            <div class="relative flex-1 max-w-md w-full">
                <select name="course_id" onchange="this.form.submit()" class="w-full px-5 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none font-semibold text-gray-700 shadow-sm">
                    <?php foreach($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $selected_course == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?> (Semester <?= $c['semester'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
        </form>

        <!-- Notification -->
        <?php if ($success_msg): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center gap-2">
                <span>✅</span> <?= $success_msg ?>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center gap-2">
                <span>⚠️</span> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <!-- Management Tools -->
        <?php if($selected_course): ?>
        <div class="mb-8 p-6 bg-blue-50/80 rounded-2xl border border-blue-100 shadow-inner">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                <h3 class="font-bold text-gray-800 flex items-center gap-2 text-lg">
                    <span>👥</span> Kelola Peserta
                </h3>
                <a href="export_students_pdf.php?course_id=<?= $selected_course ?>" target="_blank"
                   class="bg-red-600 hover:bg-red-500 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg transition flex items-center gap-2 text-sm w-full md:w-auto justify-center">
                    <span>📄</span> Rekap PDF
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Add Single -->
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="enroll_student" value="1">
                    <select name="student_id" required class="flex-1 px-4 py-3 rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">+ Tambah Satu Mahasiswa</option>
                        <?php foreach($all_students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="bg-white hover:bg-blue-50 text-blue-600 border border-blue-200 px-6 py-3 rounded-xl font-bold transition shadow-sm">
                        Tambah
                    </button>
                </form>
                <!-- Bulk Class -->
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="enroll_class" value="1">
                    <select name="class_id" required class="flex-1 px-4 py-3 rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">+ Tambah Satu Kelas (Bulk)</option>
                        <?php foreach($all_classes as $c): ?>
                            <option value="<?= $c['id_kelas'] ?>"><?= htmlspecialchars($c['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition">
                        Import
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Student List Table -->
        <?php if(empty($students)): ?>
            <div class="text-center py-16 text-gray-500 bg-white/50 rounded-2xl border border-dashed border-gray-300">
                <p class="text-xl font-semibold">Belum ada mahasiswa di kelas ini.</p>
                <p class="text-sm mt-2">Silakan tambahkan mahasiswa menggunakan form di atas.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm bg-white">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-4 px-6 font-bold">No</th>
                            <th class="py-4 px-6 font-bold">Nama Mahasiswa</th>
                            <th class="py-4 px-6 font-bold">Email</th>
                            <th class="py-4 px-6 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-medium">
                        <?php foreach($students as $idx => $s): ?>
                            <tr class="border-b border-gray-100 hover:bg-blue-50 transition">
                                <td class="py-4 px-6"><?= $idx + 1 ?></td>
                                <td class="py-4 px-6 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                                        <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                    </div>
                                    <span class="font-bold text-gray-800"><?= htmlspecialchars($s['name']) ?></span>
                                </td>
                                <td class="py-4 px-6"><?= htmlspecialchars($s['email']) ?></td>
                                <td class="py-4 px-6 text-center">
                                    <form method="POST" onsubmit="return confirm('Hapus <?= addslashes($s['name']) ?> dari kelas ini?');">
                                        <input type="hidden" name="unenroll_student" value="1">
                                        <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                        <button class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right text-sm text-gray-100 font-semibold bg-blue-900/40 inline-block px-4 py-2 rounded-lg float-right">
                Total: <?= count($students) ?> Mahasiswa
            </div>
        <?php endif; ?>

    </div>
=======
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex text-gray-800">

    <!-- Include Shared Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full transition-all duration-300 md:ml-72">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[20%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[20%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-7xl mx-auto pt-20 md:pt-10">
            <!-- Header -->
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <a href="dashboard_dosen.php" class="bg-white/10 hover:bg-white/20 p-2 rounded-xl transition text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold mb-2 text-white">Daftar Mahasiswa</h1>
                        <p class="text-blue-200">Lihat mahasiswa yang terdaftar di kelas Anda.</p>
                    </div>
                </div>
            </div>

            <!-- Filter & List Card -->
            <div class="glass rounded-3xl p-8 md:p-10 shadow-xl min-h-[500px]">
                
                <!-- Filter Form -->
                <form method="GET" class="mb-8 flex items-center gap-4">
                    <label class="font-bold text-gray-700 whitespace-nowrap">Pilih Kelas:</label>
                    <div class="relative flex-1 max-w-md">
                        <select name="course_id" onchange="this.form.submit()" class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none font-semibold text-gray-700">
                            <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $selected_course == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?> (Semester <?= $c['semester'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </form>

                <!-- Notification -->
                <?php if ($success_msg): ?>
                    <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center gap-2">
                        <span>✅</span> <?= $success_msg ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center gap-2">
                        <span>⚠️</span> <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <!-- Management Tools -->
                <?php if($selected_course): ?>
                <div class="mb-8 p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-700 flex items-center gap-2">
                            <span>👥</span> Kelola Peserta
                        </h3>
                        <a href="export_students_pdf.php?course_id=<?= $selected_course ?>" target="_blank"
                           class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl font-bold shadow-lg transition flex items-center gap-2 text-sm">
                            <span>📄</span> Rekap PDF
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Add Single -->
                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="enroll_student" value="1">
                            <select name="student_id" required class="flex-1 px-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">+ Tambah Satu Mahasiswa</option>
                                <?php foreach($all_students as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="bg-white hover:bg-blue-50 text-blue-600 border border-blue-200 px-4 py-2 rounded-xl font-bold transition">
                                Tambah
                            </button>
                        </form>
                        <!-- Bulk Class -->
                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="enroll_class" value="1">
                            <select name="class_id" required class="flex-1 px-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">+ Tambah Satu Kelas (Bulk)</option>
                                <?php foreach($all_classes as $c): ?>
                                    <option value="<?= $c['id_kelas'] ?>"><?= htmlspecialchars($c['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold shadow-lg transition">
                                Import
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Student List Table -->
                <?php if(empty($students)): ?>
                    <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <p class="text-lg">Belum ada mahasiswa di kelas ini.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-4 px-6 font-bold">No</th>
                                    <th class="py-4 px-6 font-bold">Nama Mahasiswa</th>
                                    <th class="py-4 px-6 font-bold">Email</th>
                                    <th class="py-4 px-6 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-medium">
                                <?php foreach($students as $idx => $s): ?>
                                    <tr class="border-b border-gray-200 hover:bg-blue-50 transition">
                                        <td class="py-4 px-6"><?= $idx + 1 ?></td>
                                        <td class="py-4 px-6 flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                                                <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                            </div>
                                            <span class="font-bold text-gray-800"><?= htmlspecialchars($s['name']) ?></span>
                                        </td>
                                        <td class="py-4 px-6"><?= htmlspecialchars($s['email']) ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <form method="POST" onsubmit="return confirm('Hapus <?= addslashes($s['name']) ?> dari kelas ini?');">
                                                <input type="hidden" name="unenroll_student" value="1">
                                                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                                <button class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition" title="Hapus">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-right text-sm text-gray-500 font-semibold">
                        Total: <?= count($students) ?> Mahasiswa
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
>>>>>>> d18683958109ae9fe0244a71fdc030651f124058
</body>
</html>
