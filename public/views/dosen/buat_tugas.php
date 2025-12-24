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
require_once __DIR__ . '/../../../app/Models/CourseModel.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';
require_once __DIR__ . '/../../../app/Models/EnrollmentModel.php';
require_once __DIR__ . '/../../../app/Services/NotificationService.php';

// --- 1. INISIALISASI & KONEKSI ---
$db = new Database();
$pdo = $db->connect();
$courseModel = new CourseModel($pdo);
$taskModel = new TaskModel($pdo);
$enrollModel = new EnrollmentModel($pdo);

// Ambil daftar mata kuliah yang diampu oleh Dosen yang sedang login
$courses = $courseModel->getByDosen($_SESSION['user']['id']);
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];
    if (strpos($deadline, 'T') !== false) $deadline = str_replace('T', ' ', $deadline) . ':00';

    if (!$title || !$deadline) {
        $error = "Judul dan deadline wajib diisi.";
    } else {
        // --- 2. UPLOAD FILE LAMPIRAN ---
        $attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/tasks/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            // Rename file dengan timestamp agar unik
            $fileName = time() . '_' . $_FILES['attachment']['name'];
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                $attachment = $fileName;
            }
        }
        
        if (!$error) {
            // --- 3. SIMPAN TUGAS KE DATABASE ---
            if ($taskModel->create($_SESSION['user']['id'], $course_id, $title, $description, $deadline, $attachment)) {
                
                // --- 4. KIRIM EMAIL NOTIFIKASI OTOMATIS ---
                // Setiap tugas baru akan memicu pengiriman email ke seluruh mahasiswa di kelas tersebut.
                
                // Inisialisasi Service Notifikasi
                $notifier = new NotificationService($pdo);
                $senderName = $_SESSION['user']['nama'];
                
                // Ambil Nama Matkul untuk keperluan email
                $course = $courseModel->find($course_id);
                $courseName = $course['name'] ?? 'Mata Kuliah';

                // Ambil daftar mahasiswa yang mengambil course ini
                $students = $enrollModel->getStudentsByCourse($course_id);
                $sentCount = 0;

                if (!empty($students)) {
                    // Restoring the preferred design
                    $deadlineTgl = date('d F Y', strtotime($deadline));
                    $deadlineJam = date('H:i', strtotime($deadline));
                    $attachmentHtml = $attachment ? "<p style='margin-top:10px; font-size:12px; color:#475569;'>📎 Ada Lampiran File</p>" : "";

                    $emailBody = "
                    <div style='font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; color: #334155;'>
                        <div style='background-color: #f8fafc; padding: 25px 30px; border-bottom: 1px solid #e2e8f0;'>
                            <h1 style='color: #0f172a; margin: 0; font-size: 20px; font-weight: 700;'>Tugas Baru</h1>
                            <p style='color: #64748b; margin-top: 5px; font-size: 14px;'>{$courseName}</p>
                        </div>
                        <div style='padding: 30px; background: #ffffff;'>
                            <p style='font-size: 15px; line-height: 1.6; margin-bottom: 20px;'>
                                Halo Mahasiswa,<br><br>
                                <strong>{$senderName}</strong> telah menerbitkan tugas baru. Silakan cek detail berikut:
                            </p>
                            <div style='background: #f1f5f9; padding: 20px; border-radius: 8px; margin-bottom: 25px;'>
                                <h3 style='margin: 0 0 10px 0; color: #0f172a; font-size: 16px;'>{$title}</h3>
                                <div style='font-size: 14px; color: #475569;'>
                                    <p style='margin: 5px 0;'><strong>Tenggat Waktu:</strong> <span style='color: #dc2626;'>{$deadlineTgl}, Pukul {$deadlineJam} WIB</span></p>
                                </div>
                                {$attachmentHtml}
                            </div>
                            <p style='color: #475569; font-size: 14px; margin-bottom: 30px; font-style: italic; border-left: 3px solid #cbd5e1; padding-left: 15px;'>
                                \"" . strip_tags($description) . "\"
                            </p>
                            <div style='text-align: center;'>
                                <a href='" . BASE_URL . "/index.php' style='background-color: #2563EB; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; display: inline-block;'>Lihat di Dashboard</a>
                            </div>
                        </div>
                        <div style='background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                            TaskAcademia - Universitas Buana Perjuangan Karawang<br>
                            &copy; " . date('Y') . "
                        </div>
                    </div>";

                    foreach ($students as $mhs) {
                        try {
                            if ($notifier->sendEmail($mhs['id'], $mhs['email'], "Tugas Baru: {$title}", $emailBody)) {
                                $sentCount++;
                            }
                        } catch (Exception $e) {
                            error_log("Email Error for {$mhs['email']}: " . $e->getMessage());
                        }
                    }
                }

                $success = $sentCount > 0 
                    ? "Tugas berhasil dipublikasikan dan $sentCount email notifikasi telah dikirim!" 
                    : "Tugas berhasil dibuat (Namun 0 email dikirim karena belum ada mahasiswa yang mengambil mata kuliah ini).";
            } else {
                $error = "Terjadi kesalahan saat menyimpan tugas.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Tugas Baru | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex text-white font-outfit">

    <?php include __DIR__ . '/../layouts/sidebar_dosen.php'; ?>

    <!-- Success Handling with SWAL (Same as Edit page) -->
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
            }).then(() => {
                window.location.href = 'dashboard.php';
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

    <main id="main-content" class="flex-1 relative overflow-y-auto w-full md:w-auto min-h-screen transition-all duration-300 md:ml-20">
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
             <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
        </div>

        <div class="p-6 md:p-10 relative z-10 max-w-4xl mx-auto pt-20 md:pt-10">
            <header class="mb-10">
                <h1 class="text-3xl font-bold text-white">Buat Tugas Baru</h1>
                <p class="text-blue-200">Publikasikan tugas dan berikan notifikasi ke mahasiswa Anda.</p>
            </header>

            <?php if ($error): ?>
                <div class="mb-6 p-4 glass bg-red-500/10 border-red-500/20 text-red-200 rounded-2xl flex items-center gap-3">
                    <span class="text-xl">⚠️</span> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="glass rounded-[2rem] overflow-hidden border border-white/10 shadow-2xl">
                <form method="POST" enctype="multipart/form-data" class="p-8 md:p-12 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Mata Kuliah</label>
                            <div class="relative group">
                                <select name="course_id" required class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-bold appearance-none transition-all cursor-pointer">
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" class="bg-slate-900"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-blue-300">▼</div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Tenggat Waktu</label>
                            <input type="datetime-local" name="deadline" required class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-bold transition-all">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Judul Tugas</label>
                        <input type="text" name="title" placeholder="Contoh: Implementasi CRUD PHP & MySQL" required class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-bold placeholder-blue-300/20 transition-all">
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Instruksi / Deskripsi</label>
                        <textarea name="description" rows="5" placeholder="Tulis instruksi pengerjaan tugas di sini..." class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-medium placeholder-blue-300/20 transition-all resize-none"></textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Lampiran File (Opsional)</label>
                        <div class="relative group">
                            <input type="file" name="attachment" class="w-full px-6 py-4 glass rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white border-white/10 font-medium transition-all file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-500">
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 pt-6">
                        <button type="submit" class="flex-1 relative group overflow-hidden rounded-2xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transition-all duration-300">
                             <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 group-hover:from-blue-500 group-hover:to-indigo-500 transition-all duration-300"></div>
                             <div class="relative py-4 flex items-center justify-center gap-2 text-white font-semibold text-lg tracking-wide">
                                <span>Publikasikan Tugas</span>
                             </div>
                        </button>
                        <a href="dashboard.php" class="px-10 py-4 rounded-2xl glass text-slate-300 hover:bg-white/10 font-bold transition-all flex items-center justify-center border border-white/10">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>