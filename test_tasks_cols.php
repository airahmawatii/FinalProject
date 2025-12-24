<?php
require_once __DIR__ . '/app/config/database.php';
$db = new Database();
$pdo = $db->connect();
if ($pdo) {
    echo "Table: tasks\n";
    $cols = $pdo->query("DESCRIBE tasks")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  {$c['Field']} ({$c['Type']})\n";
    }
}
