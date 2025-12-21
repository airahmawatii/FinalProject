<?php
/**
 * Cron System Diagnostic Tool
 * Run this in your browser to check if notifications are working
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Services/NotificationService.php';

echo "<h1>🔍 Cron Job Diagnostic Tool</h1>";
echo "<pre>";

// 1. Environment Check
echo "<h3>1. Environment Info</h3>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Timezone: " . date_default_timezone_get() . "\n";
echo "BASE_URL: " . BASE_URL . "\n";

// 2. Database Connection
echo "<h3>2. Database Check</h3>";
try {
    $db = new Database();
    $pdo = $db->connect();
    if ($pdo) {
        echo "✅ Database connected successfully.\n";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $required = ['users', 'tasks', 'courses', 'enrollments', 'notifications', 'task_completions'];
        foreach ($required as $table) {
            if (in_array($table, $tables)) {
                echo "  ✅ Table '$table' exists.\n";
            } else {
                echo "  ❌ Table '$table' is MISSING!\n";
            }
        }
    } else {
        echo "❌ Database connection failed!\n";
    }
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}

// 3. SMTP Check
echo "<h3>3. SMTP / Mailer Check</h3>";
$smtp_keys = ['SMTP_HOST', 'SMTP_USER', 'SMTP_PASS', 'SMTP_PORT'];
foreach ($smtp_keys as $key) {
    if (isset($_ENV[$key]) && !empty($_ENV[$key])) {
        $val = ($key === 'SMTP_PASS') ? '********' : $_ENV[$key];
        echo "✅ \$_ENV['$key'] is set: $val\n";
    } else {
        echo "❌ \$_ENV['$key'] is NOT set or empty!\n";
    }
}

// 4. Pending Tasks Check
echo "<h3>4. Task Check (H-1 & Hari H)</h3>";
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
echo "Checking for deadlines on: $today and $tomorrow\n";

$sql = "SELECT t.task_title, t.deadline, c.name as course_name 
        FROM tasks t 
        JOIN courses c ON t.course_id = c.id 
        WHERE DATE(t.deadline) = ? OR DATE(t.deadline) = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$today, $tomorrow]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($tasks) . " task(s) due today/tomorrow.\n";
foreach ($tasks as $t) {
    echo "  - [{$t['deadline']}] {$t['course_name']}: {$t['task_title']}\n";
}

// 5. Recent Notification Logs
echo "<h3>5. Recent Notification Logs (Last 5)</h3>";
$logs = $pdo->query("SELECT status, message, error_log, sent_at FROM notifications ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if ($logs) {
    foreach ($logs as $log) {
        echo "[{$log['sent_at']}] Status: {$log['status']} | Message: {$log['message']}\n";
        if ($log['error_log']) echo "  Error: {$log['error_log']}\n";
    }
} else {
    echo "No logs found in 'notifications' table.\n";
}

echo "</pre>";

if (isset($_GET['test_email'])) {
    echo "<h3>📧 Testing Email Send...</h3>";
    echo "<pre>";
    $test_email = $_GET['test_email'];
    $notifier = new NotificationService($pdo);
    $res = $notifier->sendEmail($_SESSION['user']['id'] ?? 0, $test_email, "Test Notification System", "<h1>Test Success!</h1><p>If you see this, your SMTP is working.</p>");
    if ($res) {
        echo "✅ Test email sent to $test_email\n";
    } else {
        echo "❌ Failed to send test email. Check error logs above.\n";
    }
    echo "</pre>";
} else {
    echo "<hr><p>To test actual email sending, add <code>?test_email=your-email@example.com</code> to the URL.</p>";
}
