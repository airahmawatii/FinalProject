<?php
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/config/config.php';

echo "Timezone: " . date_default_timezone_get() . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

$db = new Database();
$pdo = $db->connect();

if (!$pdo) {
    die("Database connection failed!\n");
}

$count = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
echo "Total notifications in log: $count\n\n";

if ($count > 0) {
    echo "Last 5 notifications:\n";
    $recent = $pdo->query("SELECT status, message, created_at, error_log FROM notifications ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($recent as $row) {
        echo "[{$row['created_at']}] Status: {$row['status']} | Msg: {$row['message']}\n";
        if ($row['error_log']) echo "  Error: {$row['error_log']}\n";
    }
} else {
    echo "No notifications logged yet.\n";
}

$taskCount = $pdo->query("SELECT COUNT(*) FROM tasks WHERE DATE(deadline) IN (CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY))")->fetchColumn();
echo "\nTasks due today/tomorrow: $taskCount\n";
