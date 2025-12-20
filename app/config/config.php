<?php
// Auto-detect BASE_URL
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);

    // Check if '/public/' is in the URL path (Standard Localhost/Subfolder Structure)
    if (strpos($_SERVER['SCRIPT_NAME'], '/public/') !== false) {
        // Extract everything up to and including '/public'
        $path = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/public/') + 7);
    } 
    // Handle case where we are at the root or standard index.php without public in URL (VHost)
    else {
        $path = '';
    }

    // Ensure no trailing slash
    $path = rtrim($path, '/');
    define('BASE_URL', $protocol . "://" . $host . $path);
} else {
    // FALLBACK FOR CRONJOB/CLI
    // Manual setup for hosting: nalasia.my.id
    // If you have a .env variable for APP_URL, use that here.
    define('BASE_URL', 'https://nalasia.my.id'); 
}

// Set default timezone to WIB (Western Indonesian Time - UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Optional: Define ROOT_PATH for server-side inclusions if needed
define('ROOT_PATH', dirname(__DIR__, 2));
?>
