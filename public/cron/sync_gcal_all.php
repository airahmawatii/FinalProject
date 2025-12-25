<?php
/**
 * Master Google Calendar Sync (Sinkronisasi Otomatis Massal)
 * 
 * Script ini dirancang untuk dijalankan via Cron Job (misal: setiap 1 jam).
 * Fungsinya adalah memindai seluruh user yang sudah "Hubungkan Kalender"
 * dan memasukkan tugas-tugas terbaru mereka ke Google Calendar secara otomatis.
 */

// 1. Load perlengkapan perang
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Services/GoogleClientService.php';
require_once __DIR__ . '/../../app/Models/TaskModel.php';

use Google\Service\Calendar;
use Google\Service\Calendar\Event;

echo "[" . date('Y-m-d H:i:s') . "] Memulai Sinkronisasi Massal Google Calendar...\n";

try {
    $db = new Database();
    $pdo = $db->connect();
    $taskModel = new TaskModel($pdo);
    
    // Inisialisasi Google Service (Akan otomatis deteksi Robot atau OAuth)
    $clientService = new GoogleClientService(); 

    // -------------------------------------------------------------------------
    // 2. Ambil Semua User yang Sudah Pernah Menghubungkan Kalender
    // -------------------------------------------------------------------------
    $sql = "SELECT id, nama, email, role, gcal_access_token as access_token, gcal_refresh_token as refresh_token, gcal_token_expires as expires 
            FROM users 
            WHERE gcal_access_token IS NOT NULL AND status = 'active'";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Ditemukan " . count($users) . " user yang memiliki koneksi Google Calendar aktif.\n";

    // -------------------------------------------------------------------------
    // 3. Proses Sinkronisasi untuk Tiap User
    // -------------------------------------------------------------------------
    foreach ($users as $user) {
        echo "Sedang memproses User: {$user['nama']} ({$user['role']})...\n";

        // A. Validasi & Auto-Refresh Token
        // Jika token mati, fungsi ini otomatis minta kunci baru ke Google.
        if (!$clientService->authorizeAndGetTokens($user, $user['id'], $pdo)) {
            echo "  x Lewati: Token tidak valid atau tidak bisa direfresh.\n";
            continue;
        }

        $client = $clientService->getClient();
        $calendarService = new Calendar($client);
        
        // B. Tentukan Target Kalender (Mode Hybrid)
        // Jika pakai Robot: Targetnya adalah email user.
        // Jika pakai Login OAuth: Targetnya adalah 'primary' (kalender utama user).
        $targetCalendarId = 'primary';
        if ($clientService->isServiceAccount()) {
            $targetCalendarId = $user['email'];
        }

        // C. Ambil Daftar Tugas yang Masih Berjalan
        if ($user['role'] === 'dosen') {
            $tasks = $taskModel->getByDosen($user['id']);
        } else {
            // Khusus Mahasiswa: Ambil tugas dari matkul yang mereka ikuti
            $mSql = "SELECT t.*, c.name as course_name 
                    FROM tasks t
                    JOIN courses c ON t.course_id = c.id
                    JOIN enrollments e ON c.id = e.course_id
                    WHERE e.student_id = ? AND t.deadline > NOW()";
            $mStmt = $pdo->prepare($mSql);
            $mStmt->execute([$user['id']]);
            $tasks = $mStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // D. Masukkan Tugas ke Google Calendar (Jika belum ada)
        $syncCount = 0;
        foreach ($tasks as $task) {
            $deadline = strtotime($task['deadline']);
            if ($deadline < time()) continue; // Abaikan yang sudah lewat

            $title = "[TaskAcademia] " . $task['task_title'];
            $desc = "Mata Kuliah: " . ($task['course_name'] ?? 'Umum') . "\n" . ($task['description'] ?? '');
            
            // Atur waktu (misal: pengerjaan 1 jam sebelum deadline)
            $startRFC = date(DateTime::RFC3339, $deadline - 3600);
            $endRFC   = date(DateTime::RFC3339, $deadline);

            // CEK DUPLIKAT: Jangan masukkan tugas yang judulnya sama di jam yang sama
            $optParams = [
                'q' => $title, 
                'timeMin' => $startRFC,
                'timeMax' => date(DateTime::RFC3339, $deadline + 60),
                'singleEvents' => true
            ];

            try {
                $results = $calendarService->events->listEvents($targetCalendarId, $optParams);
                if (count($results->getItems()) == 0) {
                    // Buat Event Baru
                    $event = new Event([
                        'summary' => $title,
                        'description' => $desc,
                        'start' => ['dateTime' => $startRFC, 'timeZone' => 'Asia/Jakarta'],
                        'end' => ['dateTime' => $endRFC, 'timeZone' => 'Asia/Jakarta'],
                        'reminders' => [
                            'useDefault' => false,
                            'overrides' => [['method' => 'popup', 'minutes' => 60]]
                        ]
                    ]);
                    $calendarService->events->insert($targetCalendarId, $event);
                    $syncCount++;
                }
            } catch (Exception $e) {
                echo "  ! Error Kalender untuk user {$user['id']}: " . $e->getMessage() . "\n";
                // Jika akses ditolak (misal izin dicabut user), berhenti proses user ini.
                break; 
            }
        }
        echo "  + Berhasil sinkronisasi $syncCount tugas baru.\n";
    }

    echo "--- Sinkronisasi Massal SELESAI ---\n";

} catch (Exception $e) {
    echo "GAGAL FATAL: " . $e->getMessage() . "\n";
}
