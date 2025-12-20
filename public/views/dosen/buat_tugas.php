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
require_once __DIR__ . '/../../../app/Models/TaskModel.php';
require_once __DIR__ . '/../../../app/Models/EnrollmentModel.php';

$db = new Database();
$pdo = $db->connect();
$courseModel = new CourseModel($pdo);
$taskModel = new TaskModel($pdo);
$enrollModel = new EnrollmentModel($pdo);
$courses = $courseModel->getByDosen($_SESSION['user']['id']);
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Keep existing logic unchanged) ...
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];
    if (strpos($deadline, 'T') !== false) $deadline = str_replace('T', ' ', $deadline) . ':00';

    if (!$title || !$deadline) {
        $error = "Judul dan deadline wajib diisi.";
    } else {
        $attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
             // ... (Keep file upload logic) ...
            $uploadDir = __DIR__ . '/../../uploads/tasks/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . $_FILES['attachment']['name'];
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
                $attachment = $fileName;
            }
        }
        if (!$error) {
                if ($taskModel->create($_SESSION['user']['id'], $course_id, $title, $description, $deadline, $attachment)) {
                    
                    // --- START EMAIL NOTIFICATION LOGIC ---
                    require_once __DIR__ . '/../../../app/Services/NotificationService.php';
                    $notifier = new NotificationService($pdo);
                    $senderName = $_SESSION['user']['nama'];
                    
                    // Get Course Name
                    $courseName = "Mata Kuliah";
                    foreach($courses as $c) {
                        if ($c['id'] == $course_id) {
                            $courseName = $c['name'];
                            break;
                        }
                    }

                    // Email Body Template
                    $deadlineTgl = date('d F Y', strtotime($deadline));
                    $deadlineJam = date('H:i', strtotime($deadline));
                    $attachmentHtml = $attachment ? "<p style='margin-top:10px; font-size:12px; color:#475569;'>📎 Ada Lampiran File</p>" : "";

                    $emailBody = "
                        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
                            <!-- Header -->
                            <div style='background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 40px 30px; text-align: center;'>
                                <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 800;'>Tugas Baru 🚀</h1>
                                <p style='color: #bfdbfe; margin-top: 5px; font-size: 16px; font-weight: bold;'>$courseName</p>
                                <p style='color: #bfdbfe; margin-top: 0; font-size: 14px;'>$title</p>
                            </div>

                            <!-- Content -->
                            <div style='padding: 30px; background: #ffffff;'>
                                <p style='color: #334155; font-size: 16px; line-height: 1.6;'>
                                    Halo Mahasiswa,<br>
                                    <strong>$senderName</strong> baru saja memberikan tugas baru untuk mata kuliah <strong>$courseName</strong>.
                                </p>

                                <div style='background: #f8fafc; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 8px; margin: 25px 0;'>
                                    <p style='margin: 0 0 10px 0; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;'>Deadline</p>
                                    <h2 style='margin: 0; color: #dc2626; font-size: 20px;'>$deadlineTgl</h2>
                                    <p style='margin: 0; color: #dc2626; font-weight: bold;'>Pukul $deadlineJam WIB</p>
                                    $attachmentHtml
                                </div>

                                <p style='color: #64748b; font-size: 14px; margin-bottom: 30px;'>
                                    <em>\"$description\"</em>
                                </p>

                                <div style='text-align: center;'>
                                    <a href='" . BASE_URL . "/index.php' style='background-color: #2563EB; color: white; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);'>
                                        Buka Dashboard
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div style='background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8;'>
                                &copy; " . date('Y') . " TaskAcademia - Universitas Buana Perjuangan Karawang
                            </div>
                        </div>
                    ";

                    // 1. Send to TEST EMAIL if provided
                    if (!empty($_POST['test_email'])) {
                        $testEmail = trim($_POST['test_email']);
                        // Use current user ID for test email log
                        $notifier->sendEmail($_SESSION['user']['id'], $testEmail, "[$courseName] Tugas Baru: $title", $emailBody);
                    }

                    // 2. Send to ALL STUDENTS enrolled
                    $students = $enrollModel->getStudentsByCourse($course_id);
                    foreach ($students as $mhs) {
                        try {
                            $notifier->sendEmail($mhs['id'], $mhs['email'], "[$courseName] Tugas Baru: $title", $emailBody);
                        } catch (Exception $e) {
                            // Continue sending to others even if one fails
                            error_log("Failed sending to {$mhs['email']}");
                        }
                    }
                    // --- END EMAIL NOTIFICATION LOGIC ---

                    // 3. Create Google Calendar Event (if connected)
                    require_once __DIR__ . '/../../../app/Services/CalendarService.php';
                    // Reload user to get fresh tokens
                    $user = $pdo->query("SELECT * FROM users WHERE id=" . $_SESSION['user']['id'])->fetch(PDO::FETCH_ASSOC);
                    
                    if (!empty($user['refresh_token'])) {
                        try {
                            $calendar = new CalendarService();
                            
                            // Estimate Logic: Event lasts 1 hour by default? Or All Day?
                            // Let's make it a 1-hour block at the deadline time
                            $start = $deadline; 
                            $end = date('Y-m-d H:i:s', strtotime($deadline) + 3600); // +1 Hour

                            $calendar->createEvent($user, [
                                'summary' => "[$courseName] $title",
                                'description' => $description . "\n\nLink: " . BASE_URL,
                                'start' => $start,
                                'end' => $end
                            ]);
                        } catch (Exception $e) {
                            error_log("Gagal membuat event kalender: " . $e->getMessage());
                            // Do not show error to user, just log it.
                        }
                    }

                    $success = "Tugas berhasil dibuat, notifikasi dikirim & masuk Kalender!";
                } else {
                    $error = "Gagal menyimpan tugas.";
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buat Tugas | Dosen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-6 text-gray-800">

    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none z-0">
         <div class="absolute top-[20%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
         <div class="absolute bottom-[20%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <div class="w-full max-w-3xl glass rounded-3xl p-8 md:p-10 shadow-2xl relative z-10 my-10">
        
        <!-- Header with Back Button (Same as prodi_edit.php) -->
        <div class="mb-8">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-blue-200 hover:text-white mb-2 transition text-sm font-semibold group">
                 <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                 Kembali
            </a>
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-black">Buat Tugas Baru</h1>
            </div>
            <p class="text-blue-200 text-sm mt-1">Isi form di bawah untuk memberikan tugas ke mahasiswa.</p>
        </div>

        <?php if ($success): ?>
            <script>
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: '<?= $success ?>', showConfirmButton: true
                }).then(() => window.location.href = 'dashboard.php');
            </script>
        <?php endif; ?>
        <?php if ($error): ?>
            <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error ?>' });</script>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Mata Kuliah</label>
                    <div class="relative">
                        <select name="course_id" required class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none font-semibold text-gray-700">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (Sem. <?= htmlspecialchars($c['semester']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Deadline</label>
                    <input type="datetime-local" name="deadline" required class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-semibold text-gray-700">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Judul Tugas</label>
                <input type="text" name="title" required placeholder="Contoh: Tugas Pengganti UTS" class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-semibold text-gray-800">
            </div>
            


            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="5" placeholder="Detail instruksi..." class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium text-gray-700"></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Lampiran <span class="font-normal text-gray-400">(Opsional)</span></label>
                <input type="file" name="attachment" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm">
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold shadow-lg transition transform hover:-translate-y-1">🚀 Simpan & Kirim</button>
                <a href="dashboard.php" class="px-8 py-4 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-100 font-bold transition bg-white/50">Batal</a>
            </div>
        </form>
    </div>        </div>
    </main>
</body>
</html>