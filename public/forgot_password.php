<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/Services/NotificationService.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    $db = new Database();
    $pdo = $db->connect();
    
    // 1. Cek apakah email terdaftar
    $stmt = $pdo->prepare("SELECT id, nama FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // 2. Buat Token Random
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // 3. Simpan ke tabel password_resets
        // Hapus token lama jika ada, lalu buat yang baru
        try {
            // Hapus token lama
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
            
            // Simpan token baru
            $insert = $pdo->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())");
            $insert->execute([$email, $token]);
            
            // 4. Kirim Email Notifikasi
            $resetLink = BASE_URL . "/reset_password.php?token=" . $token;
            
            $notif = new NotificationService($pdo);
            $subject = "Reset Password - TaskAcademy";
            $body = "
                <h3>Halo, {$user['nama']}</h3>
                <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
                <p>Klik tombol di bawah ini untuk membuat password baru:</p>
                <p><a href='$resetLink' style='background:#2563EB; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
                <p>Atau copy link ini: $resetLink</p>
                <p>Link ini akan kadaluarsa dalam 1 jam.</p>
                <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            ";
            
            // Ambil ID User untuk log pengiriman email
            $notif->sendEmail($user['id'], $email, $subject, $body);
            
            $message = "Link reset password telah dikirim ke email Anda.";
            
        } catch (Exception $e) {
            // Jangan tampilkan detail error SQL ke pengguna demi keamanan
            $error = "Terjadi kesalahan sistem. Pastikan tabel database sudah siap.";
            // Untuk debugging: $error .= " " . $e->getMessage();
        }
    } else {
        // Keamanan: Jangan beritahu jika email tidak ditemukan, tapi secara UX mungkin lebih baik bilang terkirim
        $message = "Jika email terdaftar, link reset akan dikirim.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | TaskAcademy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Lupa Password?</h2>
        <p class="text-gray-500 mb-6">Masukkan email Anda untuk menerima link reset.</p>
        
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-4 font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 font-medium">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg">
                Kirim Link Reset
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm">
            <a href="<?= BASE_URL ?>/index.php" class="text-gray-500 hover:text-blue-600 font-bold">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
