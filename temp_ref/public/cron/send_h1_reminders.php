<?php
/**
 * H-1 & Hari H Deadline Reminder Script
 * Sends email notifications to students for tasks due today or tomorrow
 */

// Load dependencies
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Services/NotificationService.php';

// Initialize database
$db = new Database();
$pdo = $db->connect();
$notifier = new NotificationService($pdo);

// 1. Get Today and Tomorrow dates
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

echo "[" . date('Y-m-d H:i:s') . "] Starting Deadline Reminder Script (H-1 & Hari H)...\n";
echo "Checking for deadlines on: $today (Hari H) and $tomorrow (H-1)\n";

// 2. Query for tasks due today or tomorrow
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

echo "Found " . count($tasks) . " task(s) to process.\n\n";

if (empty($tasks)) {
    echo "No reminders to send. Exiting.\n";
    exit(0);
}

// 3. Process each task
$totalEmailsSent = 0;
$totalErrors = 0;

foreach ($tasks as $task) {
    $deadlineDateOnly = date('Y-m-d', strtotime($task['deadline']));
    $isToday = ($deadlineDateOnly === $today);
    
    // Set content based on timing
    if ($isToday) {
        $typeLabel = "HARI H";
        $subjectPrefix = "🔥 DEADLINE HARI INI";
        $headerGradient = "linear-gradient(135deg, #7f1d1d 0%, #ef4444 100%)";
        $timeContext = "HARI INI";
        $urgencyMsg = "⏰ <strong>PERHATIAN TERAKHIR!</strong> Tugas ini harus segera dikumpulkan.";
    } else {
        $typeLabel = "H-1";
        $subjectPrefix = "⏰ Reminder H-1";
        $headerGradient = "linear-gradient(135deg, #dc2626 0%, #f59e0b 100%)";
        $timeContext = "BESOK";
        $urgencyMsg = "💡 <strong>Tips:</strong> Selesaikan tugas ini lebih awal agar tidak terburu-buru.";
    }

    echo "Processing [{$typeLabel}]: [{$task['course_name']}] {$task['task_title']}\n";
    
    // Get enrolled students
    $enrollSql = "
        SELECT DISTINCT u.id, u.nama, u.email
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        WHERE e.course_id = :course_id
        AND u.role = 'mahasiswa'
        AND u.status = 'active'
    ";
    
    $enrollStmt = $pdo->prepare($enrollSql);
    $enrollStmt->execute(['course_id' => $task['course_id']]);
    $students = $enrollStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  → Sending to " . count($students) . " student(s)...\n";
    
    $deadlineTgl = date('d F Y', strtotime($task['deadline']));
    $deadlineJam = date('H:i', strtotime($task['deadline']));
    $emailSubject = "{$subjectPrefix}: {$task['course_name']}";
    
    $emailBody = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
        <div style='background: {$headerGradient}; padding: 40px 30px; text-align: center;'>
            <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 800;'>".($isToday ? "🔥 Batas Waktu Terakhir!" : "⏰ Pengingat: Deadline Besok")."</h1>
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
                <em>\"" . (mb_strimwidth($task['description'], 0, 150, "...")) . "\"</em>
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
    
    foreach ($students as $student) {
        try {
            $success = $notifier->sendEmail($student['id'], $student['email'], $emailSubject, $emailBody);
            if ($success) $totalEmailsSent++; else $totalErrors++;
        } catch (Exception $e) { $totalErrors++; }
    }
}

echo "\nSummary: Sent $totalEmailsSent emails, $totalErrors errors.\n";
exit(0);
