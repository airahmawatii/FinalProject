<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Models/UserModel.php';
require_once __DIR__ . '/../app/Services/CalendarService.php';
require_once __DIR__ . '/../app/Services/GoogleTokenService.php';

use App\Services\CalendarService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/config/config.php';
if (!isset($_SESSION['user'])) { header("Location: " . BASE_URL . "/index.php?page=login"); exit; }
$user = $_SESSION['user'];
if ($user['role'] !== 'dosen' && $user['role'] !== 'admin') { die("Akses ditolak"); }

$db = new Database();
$pdo = $db->connect();
$userModel = new UserModel($pdo);

// ambil mahasiswa target (sederhana: semua mahasiswa)
$students = $pdo->query("SELECT * FROM users WHERE role='mahasiswa'")->fetchAll();

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $start = $_POST['start_datetime']; // "2025-12-01T09:00:00"
    $end   = $_POST['end_datetime'];

    // kumpulkan email mahasiswa
    $attendees = array_map(function($r){ return $r['email']; }, $students);

    // perlu data dosen (ambil fresh dari DB untuk token)
    $dosenRow = $userModel->findById($user['id']);

    $res = CalendarService::createEventFromDosen($dosenRow, [
        'summary' => $title,
        'description' => $desc,
        'start' => $start,
        'end' => $end,
        'attendees' => $attendees
    ]);

    if (isset($res['success']) && $res['success']) {
        // Simpan juga di DB tugas (opsional) dan kirim email internal jika mau.
        $success = "Tugas telah dibuat dan undangan dikirim ke mahasiswa.";
    } else {
        $error = "Gagal membuat event: " . ($res['error'] ?? 'unknown');
    }
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Buat Tugas (Dosen)</h2>
<?php if ($success) echo "<p style='color:green'>$success</p>"; ?>
<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<form method="POST">
    <input name="title" placeholder="Judul tugas" required><br>
    <textarea name="description" placeholder="Deskripsi"></textarea><br>
    <input name="start_datetime" placeholder="2025-12-01T09:00:00" required><br>
    <input name="end_datetime" placeholder="2025-12-01T10:00:00" required><br>
    <button>Buat & Kirim</button>
</form>
</body>
</html>
