<?php
session_start();
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Services/GoogleClientService.php';
require_once __DIR__ . '/../../app/Models/TaskModel.php';

use Google\Service\Calendar;
use Google\Service\Calendar\Event;

// 1. Check Auth Mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$userId = $_SESSION['user']['id'];

try {
    $db = new Database();
    $pdo = $db->connect();
    
    // 2. Setup Client
    $clientService = new GoogleClientService();
    $client = $clientService->getClient();
    $targetCalendarId = 'primary'; // Default for OAuth
    
    // --- DETEKSI METODE SINKRONISASI (Hybrid Strategy) ---
    // Sistem akan memilih jalur terbaik:
    // 1. Jalur Robot (Service Account): Lebih stabil, tidak perlu user login terus menerus.
    // 2. Jalur User Token (OAuth): Fallback jika jalur robot belum diseting.
    if ($clientService->isServiceAccount()) {
         /* 
           MODUS SERVICE ACCOUNT (ROBOT)
           -----------------------------
           Sama seperti dosen, mahasiswa juga harus share kalender mereka ke email robot.
        */
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userEmail = $stmt->fetchColumn();
        $targetCalendarId = $userEmail;
        
        $service = new Calendar($client);
        try {
            // Verify Access & Existence
            $service->calendars->get($targetCalendarId);
        } catch (Exception $e) {
             $saEmail = $clientService->getServiceAccountEmail();
             die(json_encode([
               'success' => false, 
               'message' => "<b>Akses Ditolak! Robot dicegat satpam.</b><br><br>Bot butuh izin 'Jalur Khusus' di kalender kamu.<br><br><b>Caranya (Cuma 1x seumur hidup):</b><br>1. Buka <a href='https://calendar.google.com' target='_blank' class='text-blue-400 underline'>Google Calendar</a> di laptop.<br>2. Di menu kiri <b>'My calendars'</b>, cari namamu.<br>3. Klik <b>Titik Tiga (⋮)</b> di sebelah namamu -> Pilih <b>'Settings and sharing'</b>.<br>4. Scroll ke bagian <b>'Share with specific people'</b> -> Klik <b>Add people</b>.<br>5. Masukkan email robot ini:<br> <span class='bg-slate-700 p-1 rounded text-yellow-300 select-all font-mono'>$saEmail</span> <br>6. Penting: Ganti izinnya jadi <b>'Make changes to events'</b> -> Send.",
               'code' => 'SHARE_REQUIRED',
               'sa_email' => $saEmail
           ]));
        }
    } else {
        // --- OAUTH MODE (Login Biasa) ---
        $stmt = $pdo->prepare("SELECT gcal_access_token as access_token, gcal_refresh_token as refresh_token, gcal_token_expires as expires FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userTokens = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$clientService->authorizeAndGetTokens($userTokens, $userId, $pdo)) {
            die(json_encode(['success' => false, 'message' => 'Status: Perlu Hubungkan Ulang Kalender', 'code' => 'AUTH_REQUIRED']));
        }
        
        $service = new Calendar($client);
    }
    
    // 5. Get Tasks (Mahasiswa Specific)
    $sql = "SELECT t.*, c.name as course_name 
            FROM tasks t
            JOIN courses c ON t.course_id = c.id
            JOIN enrollments e ON c.id = e.course_id
            WHERE e.student_id = ? AND t.deadline > NOW()
            ORDER BY t.deadline ASC";
            
    $stmtTask = $pdo->prepare($sql);
    $stmtTask->execute([$userId]);
    $tasks = $stmtTask->fetchAll(PDO::FETCH_ASSOC);
    
    $count = 0;
    
    // 6. Sync Loop
    foreach ($tasks as $task) {
        $deadline = strtotime($task['deadline']);
        $title = "[Task] " . $task['task_title']; 
        $desc = "Matkul: " . $task['course_name'] . "\n" . ($task['description'] ?? '') . "\n\nLihat di TaskAcademia.";
        
        $startRFC = date(DateTime::RFC3339, $deadline - 3600); // 1 jam durasi
        $endRFC   = date(DateTime::RFC3339, $deadline);
        
        $optParams = [
            'q' => $title, 
            'timeMin' => $startRFC,
            'timeMax' => date(DateTime::RFC3339, $deadline + 60),
            'singleEvents' => true
        ];
        
        // Cek duplikat menggunakan ID Kalender yang benar (User Email / Primary)
        $results = $service->events->listEvents($targetCalendarId, $optParams);
        
        if (count($results->getItems()) == 0) {
            $event = new Event([
                'summary' => $title,
                'description' => $desc,
                'start' => [ 'dateTime' => $startRFC, 'timeZone' => 'Asia/Jakarta' ],
                'end' => [ 'dateTime' => $endRFC, 'timeZone' => 'Asia/Jakarta' ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'popup', 'minutes' => 60], 
                        ['method' => 'popup', 'minutes' => 24 * 60], 
                    ],
                ],
                'colorId' => '11' // Tomato
            ]);
            
            $service->events->insert($targetCalendarId, $event);
            $count++;
        }
    }

    echo json_encode(['success' => true, 'message' => "Berhasil sinkronisasi $count tugas ke Google Calendar!"]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
