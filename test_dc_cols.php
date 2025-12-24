<?php
require_once __DIR__ . '/app/config/database.php';
$db = new Database();
$pdo = $db->connect();
if ($pdo) {
    echo "Table: dosen_courses\n";
    $cols = $pdo->query("DESCRIBE dosen_courses")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  {$c['Field']} ({$c['Type']})\n";
    }
}
