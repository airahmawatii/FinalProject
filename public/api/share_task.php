<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Models/TaskModel.php';
require_once __DIR__ . '/../../app/Models/Notification.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$taskId = $input['task_id'] ?? null;
$friendEmail = $input['email'] ?? null;

if (!$taskId || !$friendEmail) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->connect();
    $taskModel = new TaskModel($pdo);
    
    // Get task details
    $stmt = $pdo->prepare("SELECT t.*, c.name as course_name FROM tasks t JOIN courses c ON t.course_id = c.id WHERE t.id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task) {
        $senderName = $_SESSION['user']['nama'];
        $subject = "Reminder Tugas dari Temanmu ($senderName)";
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 30px; text-align: center; color: white;'>
                    <h1 style='margin:0; font-size: 20px;'>Hei! Jangan Lupa Tugas Ini 👇</h1>
                </div>
                <div style='padding: 30px; background: white;'>
                    <p>Temanmu, <strong>$senderName</strong>, mengingatkan kamu tentang tugas ini:</p>
                    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;'>
                        <h2 style='margin-top:0; color: #1e293b;'>{$task['title']}</h2>
                        <p style='color: #64748b; font-size: 14px; text-transform: uppercase; font-weight: bold;'>Matkul: {$task['course_name']}</p>
                        <p style='color: #dc2626; font-weight: bold;'>Deadline: " . date('d M Y, H:i', strtotime($task['deadline'])) . "</p>
                    </div>
                    <p style='margin-top: 20px; color: #475569;'><em>\"Semangat ngerjainnya ya!\"</em> - $senderName</p>
                </div>
            </div>
        ";

        $notifier = new Notification();
        // Use sender's name in the "From" display
        $notifier->send($friendEmail, $subject, $body, "$senderName (via TaskAcademia)");
        
        echo json_encode(['status' => 'success', 'message' => 'Email sent']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Task not found']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
