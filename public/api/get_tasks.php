<?php
// public/api/get_tasks.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Models/TaskModel.php';

$db = new Database();
$pdo = $db->connect();
$taskModel = new TaskModel($pdo);

$tasks = [];
$role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['id'];

    // Default: Show ONLY Upcoming Tasks (Deadline >= Now) to keep calendar clean
    // If 'all' param is present, show everything
    $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
    
    if ($role === 'dosen') {
        $rawTasks = $taskModel->getByDosen($user_id); // Dosen sees everything they created
    } elseif ($role === 'mahasiswa') {
        $rawTasks = $taskModel->getByStudent($user_id);
    } else {
        $rawTasks = [];
    }

    // Filter Logic
    if (!$showAll) {
        $rawTasks = array_filter($rawTasks, function($t) {
            return strtotime($t['deadline']) >= time();
        });
        // Re-index array
        $rawTasks = array_values($rawTasks);
    }

// Format for FullCalendar & ApexCharts
// FullCalendar: title, start, end, url
// ApexCharts/Gantt: x (title), y (date range)
$data = [];

foreach ($rawTasks as $t) {
    // Convert deadline to ISO8601
    // Asumsi 'created_at' ada di tabel tasks? Kalau tidak, kita pakai deadline - 7 hari sebagai start default?
    // Mari kita cek TaskModel lagi nanti. Untuk sekarang asumsi start = created_at atau now
    
    $start = $t['created_at'] ?? date('Y-m-d H:i:s', strtotime('-3 days')); 
    $end = $t['deadline'];
    
    $data[] = [
        'id' => $t['id'],
        'title' => $t['task_title'],
        'start' => $start,
        'end' => $end,
        'description' => $t['description'],
        'course' => $t['course_name'] ?? 'General',
        // For Gantt (ApexCharts rangeBar)
        'gantt' => [
            'x' => $t['task_title'],
            'y' => [
                strtotime($start) * 1000, // Milliseconds
                strtotime($end) * 1000
            ],
            'fillColor' => '#008FFB' // Bisa diubah per matkul nanti
        ]
    ];
}

echo json_encode($data);
