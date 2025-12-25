<?php
/**
 * Universal Google Callback Handler
 * 
 * File ini adalah "Jembatan" utama saat Google mengirim balik user ke sistem kita.
 * File ini cerdas karena bisa membedakan dua kondisi:
 * 1. Jika User Belum Login: Maka dilakukan proses Login/Registrasi otomatis.
 * 2. Jika User Sudah Login: Maka dilakukan proses Menyambungkan Google Calendar.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- DETEKSI LOKASI FILE (Agar jalan di Local & Hosting) ---
$baseDir = __DIR__;
if (file_exists($baseDir . '/../app/config/config.php')) {
    // Struktur Localhost (/public/google_callback.php)
    require_once $baseDir . '/../app/config/config.php';
    require_once $baseDir . '/../app/config/database.php';
    require_once $baseDir . '/../app/Services/GoogleClientService.php';
    require_once $baseDir . '/../app/Controllers/AuthController.php';
    require_once $baseDir . '/../app/Models/UserModel.php';
} elseif (file_exists($baseDir . '/app/config/config.php')) {
    // Struktur Hosting (Root / google_callback.php)
    require_once $baseDir . '/app/config/config.php';
    require_once $baseDir . '/app/config/database.php';
    require_once $baseDir . '/app/Services/GoogleClientService.php';
    require_once $baseDir . '/app/Controllers/AuthController.php';
    require_once $baseDir . '/app/Models/UserModel.php';
} else {
    // Jika file config tidak ketemu sama sekali
    die("Error: File konfigurasi tidak ditemukan. Cek struktur folder.");
}

// -------------------------------------------------------------------------
// ALUR 1: PROSES LOGIN (Jika di sesi browser belum ada User)
// -------------------------------------------------------------------------
if (!isset($_SESSION['user'])) {
    $db = new Database();
    $conn = $db->connect();
    $auth = new AuthController($conn);
    // Serahkan proses verifikasi login ke AuthController
    $auth->googleCallback(); 
    exit;
}

// -------------------------------------------------------------------------
// ALUR 2: PROSES HUBUNGKAN KALENDER (Jika User sudah masuk ke aplikasi)
// -------------------------------------------------------------------------
if (isset($_GET['code'])) {
    // Gunakan mode OAuth untuk mendapatkan akses personal user
    $service = new GoogleClientService(true);
    $client = $service->getClient();

    try {
        // Tukarkan "Kode Otorisasi" dari Google menjadi "Token Akses"
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception("Gagal mengambil token: " . $token['error']);
        }

        // Ambil data token penting
        $accessToken = $token['access_token'];
        $refreshToken = $token['refresh_token'] ?? null; // Kunci cadangan (refresh token)
        $expiresAt = time() + $token['expires_in'];

        $db = new Database();
        $pdo = $db->connect();
        $userModel = new UserModel($pdo);
        
        // Simpan token tersebut ke database (khusus kolom gcal_)
        $userModel->updateGcalTokens($_SESSION['user']['id'], $accessToken, $refreshToken, $expiresAt);
        
        // Tandai di sesi (session) bahwa kalender sudah aktif
        $_SESSION['user']['gcal_connected'] = true;
        
        // Tentukan arah balik setelah sukses berdasarkan jabatan (Dosen/Mhs)
        $role = $_SESSION['user']['role'];
        $redirectUrl = ($role === 'dosen') ? BASE_URL . '/views/dosen/dashboard.php' : BASE_URL . '/views/mahasiswa/dashboard_mahasiswa.php';
        
        // Kembali ke dashboard dengan pesan sukses
        header("Location: $redirectUrl?msg=" . urlencode("Google Calendar Berhasil Terhubung! 📅")); 
        exit;

    } catch (Exception $e) {
        // Tampilkan error jika terjadi kegagalan saat menyambungkan
        die("Terjadi kesalahan saat menyambungkan kalender: " . $e->getMessage());
    }
} else {
    // Jika tidak ada kode dari google, balikkan ke halaman depan
    header("Location: " . BASE_URL . "/index.php");
    exit;
}
