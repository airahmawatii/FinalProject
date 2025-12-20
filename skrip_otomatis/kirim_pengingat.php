<?php
/**
 * Cron Job Script: Send Deadline Reminders (H-1)
 * Run this script daily via cron or scheduler.
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Services/NotificationService.php';

try {
    echo "=== STARTING REMINDER JOB ===\n";
    echo "Date: " . date('Y-m-d H:i:s') . "\n";

    $db = new Database();
    $pdo = $db->connect();
    $notifier = new NotificationService($pdo);

    // Debug: Check Current Date in DB
    $dbDate = $pdo->query("SELECT CURDATE()")->fetchColumn();
    echo "DB Date: $dbDate\n";
    echo "Target Date: " . date('Y-m-d', strtotime('+1 day')) . "\n";

    // 1. Get tasks due TOMORROW (H-1)
    $targetDate = date('Y-m-d', strtotime('+1 day'));
    echo "Using PHP Target Date: $targetDate\n";

    $sql = "
        SELECT t.id as task_id, t.task_title, t.deadline, DATE(t.deadline) as deadline_date, c.name as course_name, c.id as course_id
        FROM tasks t
        JOIN courses c ON t.course_id = c.id
        WHERE DATE(t.deadline) = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$targetDate]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($tasks) . " tasks due tomorrow.\n";
    if (count($tasks) === 0) {
        echo "Debug: Listing ALL tasks to check dates:\n";
        $allTasks = $pdo->query("SELECT id, task_title, DATE(deadline) as d FROM tasks")->fetchAll(PDO::FETCH_ASSOC);
        foreach($allTasks as $at) echo " - [{$at['id']}] {$at['task_title']} Due: {$at['d']}\n";
    }

    $totalSent = 0;

    foreach ($tasks as $task) {
        echo "Processing Task: " . $task['task_title'] . " (" . $task['course_name'] . ")\n";

        // 2. Get students enrolled in this course
        $studentStmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.email, u.nama, u.status 
            FROM users u
            JOIN enrollments e ON e.student_id = u.id
            WHERE e.course_id = ? AND u.role = 'mahasiswa'
        ");
        
        $studentStmt->execute([$task['course_id']]);
        $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "  - Found " . count($students) . " students enrolled in Course #{$task['course_id']}.\n";
        
        foreach ($students as $student) {
            echo "    Checking student: {$student['email']} (Status: {$student['status']})\n";
            if ($student['status'] !== 'active') {
                echo "    -> SKIP (Not active)\n";
                continue;
            }

            // 3. Send Email
            $subject = "⏰ Reminder: [{$task['course_name']}] Tugas H-1 Deadline - " . $task['task_title'];
            $body = "
                <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #d9534f;'>Peringatan Deadline!</h2>
                    <p>Halo <b>{$student['nama']}</b>,</p>
                    <p>Jangan lupa, kamu memiliki tugas yang harus dikumpulkan <b>BESOK</b>:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 8px; font-weight: bold;'>Mata Kuliah:</td>
                            <td style='padding: 8px;'>{$task['course_name']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; font-weight: bold;'>Judul Tugas:</td>
                            <td style='padding: 8px;'>{$task['task_title']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; font-weight: bold;'>Deadline:</td>
                            <td style='padding: 8px; color: red; font-weight: bold;'>" . date('d M Y, H:i', strtotime($task['deadline'])) . "</td>
                        </tr>
                    </table>

                    <p>Segera kumpulkan sebelum terlambat!</p>
                    <br>
                    <p style='color: #888; font-size: 12px;'><i>Pesan otomatis oleh TaskAcademia System.</i></p>
                </div>
            ";

            if ($notifier->sendEmail($student['id'], $student['email'], $subject, $body)) {
                echo "    ✓ Sent to: {$student['email']}\n";
                $totalSent++;
            } else {
                echo "    ✗ Failed to: {$student['email']}\n";
            }
        }
    }

    echo "=== JOB COMPLETED. Total emails sent: $totalSent ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
