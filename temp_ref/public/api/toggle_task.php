<?php
session_start();
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Models/TaskModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['task_id'] ?? null;

if (!$taskId) {
    echo json_encode(['status' => 'error', 'message' => 'Task ID required']);
    exit;
}

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);

try {
    $isCompleted = $taskModel->toggleCompletion($taskId, $_SESSION['user']['id']);
    echo json_encode([
        'status' => 'success', 
        'is_completed' => $isCompleted,
        'message' => $isCompleted ? 'Tugas ditandai selesai!' : 'Tugas ditandai belum selesai.'
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
