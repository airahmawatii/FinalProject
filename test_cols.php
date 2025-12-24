<?php
require_once __DIR__ . '/app/config/database.php';
$db = new Database();
$pdo = $db->connect();
if ($pdo) {
    $tables = ['users', 'mahasiswa', 'dosen'];
    foreach ($tables as $t) {
        echo "Table: $t\n";
        $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  {$c['Field']} ({$c['Type']})\n";
        }
    }
}
