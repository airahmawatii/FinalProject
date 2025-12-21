<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/Models/UserModel.php';

header('Content-Type: application/json');

// Check Admin Role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No valid file uploaded']);
    exit;
}

$file = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file, "r");

if ($handle === FALSE) {
    echo json_encode(['status' => 'error', 'message' => 'Could not open file']);
    exit;
}

$db = new Database();
$pdo = $db->connect();
$userModel = new UserModel($pdo);

$successCount = 0;
$failCount = 0;
$errors = [];
$row = 0;

try {
    $pdo->beginTransaction();

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row++;
        // Skip header if exists (assume header contains 'Nama' or 'Name')
        if ($row === 1 && (stripos($data[0], 'nama') !== false || stripos($data[0], 'name') !== false)) {
            continue;
        }

        // Expected Format: Name, Email
        // Optional: NIM (can be part of email username)
        $name = trim($data[0] ?? '');
        $email = trim($data[1] ?? '');
        
        if (empty($name) || empty($email)) {
            $failCount++;
            $errors[] = "Row $row: Missing name or email";
            continue;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $failCount++;
            $errors[] = "Row $row: Invalid email format ($email)";
            continue;
        }

        // Check duplicate
        if ($userModel->findByEmail($email)) {
            $failCount++;
            $errors[] = "Row $row: Email already exists ($email)";
            continue;
        }

        // Create User
        // Default password: mhs123
        $defaultPassword = password_hash('mhs123', PASSWORD_DEFAULT);
        
        // Insert directly using PDO for custom logic if needed, or UserModel
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, status) VALUES (?, ?, ?, 'mahasiswa', 'active')");
        if ($stmt->execute([$name, $email, $defaultPassword])) {
            $successCount++;
        } else {
            $failCount++;
            $errors[] = "Row $row: Database insertion failed";
        }
    }

    $pdo->commit();
    fclose($handle);

    echo json_encode([
        'status' => 'success',
        'message' => "Import completed. Success: $successCount, Failed: $failCount",
        'details' => $errors
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    fclose($handle);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
