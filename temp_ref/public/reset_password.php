<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (!$token) {
    die("Token tidak valid.");
}

$db = new Database();
$pdo = $db->connect();

// Verify Token
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$stmt->execute([$token]);
$resetRequest = $stmt->fetch();

if (!$resetRequest) {
    $error = "Link tidak valid atau sudah kadaluarsa.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // Reset Password
        $email = $resetRequest['email'];
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Update User
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->execute([$hash, $email]);
        
        // Delete Token
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        
        $success = "Password berhasil diubah! Silakan login.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | TaskAcademy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Reset Password</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-4 font-medium border border-red-100">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php if (strpos($error, 'kadaluarsa') !== false): ?>
                <a href="forgot_password.php" class="block text-center text-blue-600 font-bold mb-4">Request Ulang</a>
            <?php endif; ?>
        <?php elseif ($success): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 font-medium border border-green-100 text-center">
                <p class="text-xl mb-2">🎉</p>
                <?= htmlspecialchars($success) ?>
                <a href="<?= BASE_URL ?>/index.php" class="block mt-4 bg-green-600 text-white py-2 rounded-lg font-bold hover:bg-green-700">Login Sekarang</a>
            </div>
        <?php else: ?>
            <p class="text-gray-500 mb-6">Masukkan password baru Anda.</p>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg">
                    Simpan Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
