<?php
/**
 * Deadline Reminder Script (H-1 & Hari H)
 * 
 * Script ini dirancang untuk dijalankan secara otomatis (Cron Job) setiap hari.
 * Tugas utamanya: Mencari tugas yang kumpul besok atau hari ini, lalu
 * mengirim email pengingat kepada mahasiswa yang belum mengerjakan.
 */

// 1. Load semua pengaturan dan library yang dibutuhkan
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Services/NotificationService.php';

// 2. Persiapan Database dan Kurir Email
$db = new Database();
$pdo = $db->connect();
$notifier = new NotificationService($pdo);

// 3. Tentukan tanggal Hari Ini dan Besok
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

echo "[" . date('Y-m-d H:i:s') . "] Memulai Script Pengingat Deadline (H-1 & Hari H)...\n";
echo "Mengecek deadline untuk tanggal: $today (Hari H) dan $tomorrow (H-1)\n";

// -------------------------------------------------------------------------
// 4. Cari Tugas Berdasarkan Tanggal Deadline
// -------------------------------------------------------------------------
// CATATAN PENTING: 
// - Timezone database sudah di-set ke WIB (+07:00) di database.php
// - Jadi DATE(t.deadline) akan otomatis menggunakan waktu Indonesia
// - Tidak perlu CONVERT_TZ karena sudah di-handle di level koneksi
$sql = "
    SELECT 
        t.id,
        t.task_title,
        t.description,
        t.deadline,
        t.dosen_id,
        c.id as course_id,
        c.name as course_name,
        u.nama as dosen_name
    FROM tasks t
    JOIN courses c ON t.course_id = c.id
    JOIN users u ON t.dosen_id = u.id
    WHERE DATE(t.deadline) = :today 
    OR DATE(t.deadline) = :tomorrow
    ORDER BY t.deadline ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'today' => $today,
    'tomorrow' => $tomorrow
]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Ditemukan " . count($tasks) . " tugas yang perlu diproses.\n\n";

if (empty($tasks)) {
    echo "Tidak ada pengingat yang perlu dikirim hari ini. Keluar.\n";
    exit(0);
}

// -------------------------------------------------------------------------
// 5. Proses Setiap Tugas Satu Per Satu
// -------------------------------------------------------------------------
$totalEmailsSent = 0;
$totalErrors = 0;

foreach ($tasks as $task) {
    $deadlineDateOnly = date('Y-m-d', strtotime($task['deadline']));
    $isToday = ($deadlineDateOnly === $today);
    
    // Sesuaikan tampilan email berdasarkan sisa waktu (Hari H warnanya merah, H-1 warnanya kuning/merah muda)
    if ($isToday) {
        $typeLabel = "HARI H";
        $subjectPrefix = "DEADLINE HARI INI";
        $headerGradient = "linear-gradient(135deg, #7f1d1d 0%, #ef4444 100%)";
        $timeContext = "HARI INI";
        $urgencyMsg = "<strong>PERHATIAN TERAKHIR!</strong> Tugas ini harus segera dikumpulkan.";
    } else {
        $typeLabel = "H-1";
        $subjectPrefix = "Reminder H-1";
        $headerGradient = "linear-gradient(135deg, #dc2626 0%, #f59e0b 100%)";
        $timeContext = "BESOK";
        $urgencyMsg = "<strong>Tips:</strong> Selesaikan tugas ini lebih awal agar tidak terburu-buru.";
    }

    echo "Memproses [{$typeLabel}]: [{$task['course_name']}] {$task['task_title']}\n";
    
    // -------------------------------------------------------------------------
    // 6. Cari Mahasiswa yang BELUM MENGUMPULKAN Tugas Ini
    // -------------------------------------------------------------------------
    $enrollSql = "
        SELECT DISTINCT u.id, u.nama, u.email
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        WHERE e.course_id = :course_id
        AND u.role = 'mahasiswa'
        AND u.status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM task_completions tc 
            WHERE tc.user_id = u.id 
            AND tc.task_id = :task_id
        )
    ";
    
    $enrollStmt = $pdo->prepare($enrollSql);
    $enrollStmt->execute([
        'course_id' => $task['course_id'],
        'task_id' => $task['id']
    ]);
    $students = $enrollStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  → Target pengiriman: " . count($students) . " mahasiswa...\n";
    
    // -------------------------------------------------------------------------
    // 7. Siapkan Desain Email (HTML)
    // -------------------------------------------------------------------------
    $deadlineTgl = date('d F Y', strtotime($task['deadline']));
    $deadlineJam = date('H:i', strtotime($task['deadline']));
    $emailSubject = "{$subjectPrefix}: {$task['course_name']}";
    
    $emailBody = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
        <div style='background: {$headerGradient}; padding: 40px 30px; text-align: center;'>
            <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 800;'>".($isToday ? "Batas Waktu Terakhir!" : "Pengingat: Deadline Besok!")."</h1>
            <p style='color: rgba(255,255,255,0.9); margin-top: 5px; font-size: 16px; font-weight: bold;'>{$task['course_name']}</p>
        </div>

        <div style='padding: 30px; background: #ffffff;'>
            <p style='color: #334155; font-size: 16px; line-height: 1.6;'>
                Halo Mahasiswa,<br>
                Kami ingin mengingatkan bahwa tugas <strong>{$task['task_title']}</strong> akan mencapai batas waktu <strong>{$timeContext}</strong>!
            </p>

            <div style='background: ".($isToday ? "#fff1f2" : "#fef2f2")."; border-left: 4px solid ".($isToday ? "#be123c" : "#dc2626")."; padding: 20px; border-radius: 8px; margin: 25px 0;'>
                <p style='margin: 0 0 10px 0; font-size: 12px; color: ".($isToday ? "#9f1239" : "#991b1b")."; text-transform: uppercase; font-weight: bold; letter-spacing: 1px;'>⚠️ ".($isToday ? "DEADLINE HARI INI" : "DEADLINE BESOK")."</p>
                <h2 style='margin: 0; color: ".($isToday ? "#be123c" : "#dc2626")."; font-size: 20px;'>{$deadlineTgl}</h2>
                <p style='margin: 0; color: ".($isToday ? "#be123c" : "#dc2626")."; font-weight: bold;'>Pukul {$deadlineJam} WIB</p>
            </div>

            <p style='color: #64748b; font-size: 14px; margin-bottom: 30px;'>
                <strong>Deskripsi Tugas:</strong><br>
                <em>\"" . (mb_strimwidth(strip_tags($task['description']), 0, 150, "...")) . "\"</em>
            </p>

            <div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 30px;'>
                <p style='margin: 0; color: #475569; font-size: 14px;'>
                    {$urgencyMsg}
                </p>
            </div>

            <div style='text-align: center;'>
                <a href='" . BASE_URL . "/index.php' style='background-color: ".($isToday ? "#be123c" : "#dc2626")."; color: white; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>
                    Buka Dashboard
                </a>
            </div>
        </div>
        
        <div style='background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8;'>
            &copy; " . date('Y') . " TaskAcademia - Universitas Buana Perjuangan Karawang
        </div>
    </div>
    ";
    
    // -------------------------------------------------------------------------
    // 8. Kirim Email ke Setiap Mahasiswa (Dengan Filter Anti-Spam)
    // -------------------------------------------------------------------------
    foreach ($students as $student) {
        // --- CEK DUPLIKAT (ANTI-SPAM) ---
        // Kita tidak ingin mengirim email yang sama berkali-kali jika cron job error/running ulang.
        $checkSql = "SELECT 1 FROM notifications WHERE user_id = ? AND task_id = ? AND type = ? AND status = 'sent' AND DATE(created_at) = CURDATE()";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$student['id'], $task['id'], $typeLabel]);
        
        if ($checkStmt->fetch()) {
            echo "    - Lewati {$student['nama']}: Sudah pernah dikirim hari ini.\n";
            continue;
        }

        try {
            // Panggil NotificationService untuk mengirim email asli
            $success = $notifier->sendEmail($student['id'], $student['email'], $emailSubject, $emailBody, $task['id'], $typeLabel);
            if ($success) {
                $totalEmailsSent++;
                echo "    + Sukses mengirim ke {$student['nama']}\n";
            } else {
                $totalErrors++;
                echo "    x Gagal mengirim ke {$student['nama']}\n";
            }
        } catch (Exception $e) { $totalErrors++; }
    }
}

echo "\n--- SELESAI ---\nRingkasan: Berhasil mengirim $totalEmailsSent email, ditemukan $totalErrors error.\n";
exit(0);
