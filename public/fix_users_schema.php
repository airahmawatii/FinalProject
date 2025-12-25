<?php
require_once __DIR__ . '/../app/config/database.php';
$db = new Database();
$pdo = $db->connect();

try {
    echo "Memperbaiki struktur tabel users...\n";
    
    // Set SEMUA kolom Google Token agar punya default NULL secara eksplisit
    $sql = "ALTER TABLE users 
            MODIFY COLUMN access_token TEXT NULL DEFAULT NULL,
            MODIFY COLUMN refresh_token TEXT NULL DEFAULT NULL,
            MODIFY COLUMN token_expires BIGINT NULL DEFAULT NULL,
            MODIFY COLUMN gcal_access_token TEXT NULL DEFAULT NULL,
            MODIFY COLUMN gcal_refresh_token TEXT NULL DEFAULT NULL,
            MODIFY COLUMN gcal_token_expires INT NULL DEFAULT NULL";
            
    $pdo->exec($sql);
    
    echo "Berhasil! Kolom token sekarang memiliki default NULL.\n";
    
} catch (Exception $e) {
    echo "Gagal: " . $e->getMessage() . "\n";
}
