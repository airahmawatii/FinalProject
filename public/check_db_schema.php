<?php
require_once __DIR__ . '/../app/config/database.php';
$db = new Database();
$pdo = $db->connect();

$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($columns);
echo "</pre>";
