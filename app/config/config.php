<?php
// Auto-detect BASE_URL
if (isset($_SERVER['HTTP_HOST'])) {
    // -------------------------------------------------------------------------
    // 1. Deteksi Protokol (HTTP/HTTPS)
    // -------------------------------------------------------------------------
    // Script ini secara otomatis mendeteksi apakah website diakses lewat HTTPS.
    // Ini penting untuk hosting yang menggunakan Reverse Proxy (seperti Cloudflare/Nginx)
    // agar redirect dan aset tetap aman (SSL).
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );
    
    $protocol = $isHttps ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);

    // -------------------------------------------------------------------------
    // 2. Deteksi Path URL (Subfolder/Root)
    // -------------------------------------------------------------------------
    // Cek apakah aplikasi berjalan di dalam folder '/public/' (Localhost standar)
    if (strpos($_SERVER['SCRIPT_NAME'], '/public/') !== false) {
        // Ambil path sampai ke folder '/public/'
        $path = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/public/') + 7);
    } 
    // Handle jika diakses lewat domain utama (Virtual Host / VPS)
    else {
        $path = '';
    }

    // Pastikan tidak ada slash berlebih di akhir URL
    $path = rtrim($path, '/');
    
    // DEFINISI KONSTANTA BASE_URL UTAMA
    define('BASE_URL', $protocol . "://" . $host . $path);
} else {
    // -------------------------------------------------------------------------
    // 3. Fallback untuk CLI / Cron Job
    // -------------------------------------------------------------------------
    // Saat script dijalankan lewat Terminal (Cron Job), $_SERVER['HTTP_HOST'] tidak ada.
    // Maka kita pakai URL manual dari file .env atau default hardcoded.
    $fallbackUrl = $_ENV['APP_URL'] ?? 'https://nalasia.my.id';
    define('BASE_URL', $fallbackUrl); 
}

// -------------------------------------------------------------------------
// 4. Konfigurasi Zona Waktu
// -------------------------------------------------------------------------
// Set waktu server ke WIB (Asia/Jakarta) agar deadline dan notifikasi sesuai waktu Indonesia.
date_default_timezone_set('Asia/Jakarta');

// Optional: Define ROOT_PATH for server-side inclusions if needed
define('ROOT_PATH', dirname(__DIR__, 2));
?>
