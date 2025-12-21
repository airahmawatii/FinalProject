<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Prevent HTML errors from breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Handle Fatal Errors as JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']]);
        exit;
    }
});

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Models/TaskModel.php';
require_once __DIR__ . '/../../app/Models/UserModel.php';
require_once __DIR__ . '/../../app/Services/CalendarService.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['task_id'] ?? null;

if (!$taskId) {
    echo json_encode(['status' => 'error', 'message' => 'Task ID required']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->connect();
    
    // Get Task Info
    $taskModel = new TaskModel($pdo);
    
    // Fetch Task along with Course Name
    $stmt = $pdo->prepare("
        SELECT t.*, c.name as course_name 
        FROM tasks t
        JOIN courses c ON t.course_id = c.id
        WHERE t.id = ?
    ");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        throw new Exception("Tugas tidak ditemukan");
    }

    // Get User Google Token
    $userModel = new UserModel($pdo);
    $user = $userModel->findById($_SESSION['user']['id']);
    
    if (empty($user['access_token'])) {
        $debugInfo = "User ID: " . $user['id'] . ", Token Length: " . strlen($user['access_token'] ?? '');
        throw new Exception("Akun Google belum terhubung/Token Kosong. ($debugInfo). Silakan login ulang via Google.");
    }

    // Sync to Calendar
    $calendarService = new CalendarService();
    
    // Estimate Event Duration (1 Hour from deadline)
    $start = $task['deadline'];
    $end = date('Y-m-d H:i:s', strtotime($task['deadline']) + 3600); 

    $eventLink = $calendarService->createEvent($user, [
        'summary' => "[" . $task['course_name'] . "] " . $task['task_title'],
        'description' => $task['description'] . "\n\nLink: " . BASE_URL,
        'start' => $start,
        'end' => $end
    ]);
    
    if ($eventLink) {
        echo json_encode(['status' => 'success', 'message' => 'Berhasil ditambahkan ke Google Calendar', 'link' => $eventLink]);
    } else {
        throw new Exception("Gagal sinkronisasi. Cek log server atau pastikan token valid.");
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
