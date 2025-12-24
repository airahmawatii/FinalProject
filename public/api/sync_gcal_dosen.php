<?php
session_start();
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Services/GoogleClientService.php';
require_once __DIR__ . '/../../app/Models/TaskModel.php';

use Google\Service\Calendar;
use Google\Service\Calendar\Event;

// 1. Check Auth
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$userId = $_SESSION['user']['id'];

try {
    $db = new Database();
    $pdo = $db->connect();
    
    // 2. Setup Client
    $clientService = new GoogleClientService();
    $client = $clientService->getClient();
    $targetCalendarId = 'primary';
    
    // --- CEK TIPE KONEKSI: Service Account Robot ATAU User Login? ---
    if ($clientService->isServiceAccount()) {
        /* 
           MODUS SERVICE ACCOUNT (ROBOT)
           -----------------------------
           Disini aplikasi bertindak sebagai "Robot" (Service Account).
           Karena Robot adalah entitas asing, dia TIDAK BISA langsung tulis ke kalender user.
           
           Solusi Keamanan Google:
           1. Target Kalender = Email User (Dosen) yang mau diisi.
           2. User WAJIB share kalender mereka ke Email Robot (sa-email) agar Robot punya akses "Write".
           
           Ini satu-satunya cara agar bot bisa update kalender tanpa User Login.
        */
        
        // Ambil email dosen dari database untuk dijadikan Target ID Kalender
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userEmail = $stmt->fetchColumn();
        $targetCalendarId = $userEmail;
        
        // Cek Akses: Coba baca data kalender target.
        // Jika Error (404/403) -> Berarti User belum Share kalender ke Robot.
        $service = new Calendar($client);
        try {
            $service->calendars->get($targetCalendarId);
        } catch (Exception $e) {
             // Kalo Gagal, ambil email robotnya buat dikasih tau ke user
             $saEmail = $clientService->getServiceAccountEmail();
             
             // Kirim pesan error wajar ke frontend agar User tau langkah selanjutnya
             die(json_encode([
               'success' => false, 
               'message' => "<b>Izin Akses Diperlukan 🔒</b><br><br>Bot SIKAD belum diizinkan mengakses kalender Bapak/Ibu.<br><br><b>Mohon lakukan pengaturan sekali saja:</b><br>1. Buka <a href='https://calendar.google.com' target='_blank' class='text-blue-400 underline'>Google Calendar Web</a>.<br>2. Di menu kiri bawah <b>'My calendars'</b>, cari nama Anda.<br>3. Klik <b>Titik Tiga (⋮)</b> di sebelah nama Anda -> Pilih <b>'Settings and sharing'</b>.<br>4. Scroll ke cari <b>'Share with specific people'</b> -> Klik tombol <b>Add people</b>.<br>5. Paste email robot ini:<br> <span class='bg-slate-700 p-1 rounded text-yellow-300 select-all font-mono'>$saEmail</span> <br>6. Ubah Permission menjadi <b>'Make changes to events'</b> -> Send.",
               'code' => 'SHARE_REQUIRED',
               'sa_email' => $saEmail
           ]));
        }
        
    } else {
        /*
           MODUS OAUTH (USER LOGIN)
           ------------------------
           Ini metode klasik dimana User klik tombol "Connect", login Google, dan kasih izin.
           Target kalender adalah 'primary' (kalender utama user yg sedang login).
           
           Kelemahan: Sering kena blok "Unverified App" oleh Google saat development.
        */
        
        // 2b. Ambil Token User dari Database
        $stmt = $pdo->prepare("SELECT gcal_access_token, gcal_refresh_token, gcal_token_expires FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['gcal_access_token'])) {
            die(json_encode(['success' => false, 'message' => 'Not Connected', 'code' => 'AUTH_REQUIRED']));
        }

        $client->setAccessToken($user['gcal_access_token']);

        // Cek apakah token kadaluarsa? Jika ya, Refresh otomatis agar user gak perlu login ulang.
        if ($client->isAccessTokenExpired()) {
            if (!empty($user['gcal_refresh_token'])) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($user['gcal_refresh_token']);
                if (!isset($newToken['error'])) {
                    $client->setAccessToken($newToken);
                    $upd = $pdo->prepare("UPDATE users SET gcal_access_token = ?, gcal_token_expires = ? WHERE id = ?");
                    $exp = time() + ($newToken['expires_in'] ?? 3599);
                    $upd->execute([$newToken['access_token'], $exp, $userId]);
                } else {
                    die(json_encode(['success' => false, 'message' => 'Refresh Token Error. Re-connect required.']));
                }
            } else {
                die(json_encode(['success' => false, 'message' => 'Token Expired. Re-connect required.']));
            }
        }
        $service = new Calendar($client);
    }
    
    // 5. Init Model & Tasks
    $taskModel = new TaskModel($pdo);
    $tasks = $taskModel->getByDosen($userId); 
    
    $count = 0;
    
    // 6. Sync Loop
    foreach ($tasks as $task) {
        $deadline = strtotime($task['deadline']);
        if ($deadline < time()) continue; // Skip past tasks

        $title = "[TaskAcademia] " . $task['task_title']; // Prefix to easily query/identify
        $desc = "Mata Kuliah: " . $task['course_name'] . "\n" . ($task['description'] ?? '');
        
        // Cek Duplikat: List event di rentang waktu deadline +/- 1 menit
        // (Google API list is expensive, but safer than dupes without DB ID)
        $startRFC = date(DateTime::RFC3339, $deadline - 3600); // 1 hour before
        $endRFC   = date(DateTime::RFC3339, $deadline);
        
        $optParams = [
            'q' => $title, 
            'timeMin' => $startRFC,
            'timeMax' => date(DateTime::RFC3339, $deadline + 60),
            'singleEvents' => true
        ];
        
        $results = $service->events->listEvents($targetCalendarId, $optParams);
        
        if (count($results->getItems()) == 0) {
            // Create Event
            $event = new Event([
                'summary' => $title,
                'description' => $desc,
                'start' => [
                    'dateTime' => $startRFC,
                    'timeZone' => 'Asia/Jakarta',
                ],
                'end' => [
                    'dateTime' => $endRFC,
                    'timeZone' => 'Asia/Jakarta',
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60],
                        ['method' => 'popup', 'minutes' => 60],
                    ],
                ],
            ]);
            
            $service->events->insert($targetCalendarId, $event);
            $count++;
        }
    }

    echo json_encode(['success' => true, 'message' => "Berhasil sinkronisasi $count tugas ke Google Calendar!"]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
