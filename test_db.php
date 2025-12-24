<?php
require_once __DIR__ . '/app/config/database.php';
$db = new Database();
$pdo = $db->connect();
if ($pdo) {
    echo "Connected\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables) . "\n";
} else {
    echo "Failed to connect\n";
}
