<?php
// public/dosen/hapus_tugas.php
session_start();
require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/TaskModel.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

$db = new Database(); $pdo = $db->connect();
$taskModel = new TaskModel($pdo);

$id = $_GET['id'] ?? null;
$task = $taskModel->find($id);
if (!$task || $task['dosen_id'] != $_SESSION['user']['id']) {
    die("Tidak berwenang.");
}

// Check if confirmed
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $taskModel->delete($id);
    header("Location: daftar_tugas.php?msg=deleted");
    exit;
}

// Show confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hapus Tugas</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            title: 'Yakin hapus tugas ini?',
            text: "Tugas: <?= htmlspecialchars($task['task_title']) ?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'hapus_tugas.php?id=<?= $id ?>&confirm=yes';
            } else {
                window.location.href = 'daftar_tugas.php';
            }
        });
    </script>
</body>
</html>

