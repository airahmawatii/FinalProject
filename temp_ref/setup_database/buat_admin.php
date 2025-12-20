<?php
require_once __DIR__ . '/../app/config/database.php';

$db = new Database();
$pdo = $db->connect();

$email = 'admin@taskacademia.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Administrator';
$role = 'admin';

// Check if exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "Admin already exists. Resetting password...\n";
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$password, $email]);
} else {
    echo "Creating new admin...\n";
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);
}

echo "Done. Login with: $email / admin123";
