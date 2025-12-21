<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';
require_once __DIR__ . '/../../../app/config/database.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header("Location: " . BASE_URL . "/index.php?page=login");
    exit;
}

// Verify token
try {
    $db = new Database();
    $pdo = $db->connect();
    
    // Check if token exists and not expired (1 hour)
    $stmt = $pdo->prepare("
        SELECT email, created_at 
        FROM password_resets 
        WHERE token = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reset) {
        $error = "Link reset password tidak valid atau sudah kadaluarsa.";
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirm)) {
            $error = "Password wajib diisi.";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter.";
        } elseif ($password !== $confirm) {
            $error = "Password tidak cocok.";
        } else {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $reset['email']]);
            
            // Delete used token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = "Password berhasil diubah! Silakan login dengan password baru.";
        }
    }
    
} catch (PDOException $e) {
    $error = "Terjadi kesalahan. Silakan coba lagi.";
    error_log("Reset Password Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 min-h-screen flex items-center justify-center p-4">
    
    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none">
        <div class="absolute top-[20%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[20%] left-[10%] w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[100px]"></div>
    </div>

    <!-- Form Card -->
    <div class="glass rounded-3xl p-8 md:p-10 shadow-2xl max-w-md w-full relative z-10">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔑 Reset Password</h1>
            <p class="text-gray-600">Masukkan password baru Anda</p>
        </div>

        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="text-center">
                <div class="mb-6">
                    <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-green-600 font-semibold mb-6"><?= $success ?></p>
                <a href="<?= BASE_URL ?>/index.php?page=login" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold transition">
                    Login Sekarang
                </a>
            </div>
        <?php elseif ($error && empty($_POST)): ?>
            <!-- Token Error -->
            <div class="text-center">
                <div class="mb-6">
                    <svg class="w-20 h-20 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-red-600 font-semibold mb-6"><?= $error ?></p>
                <a href="<?= BASE_URL ?>/views/auth/forgot_password.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold transition">
                    Request Ulang
                </a>
            </div>
        <?php else: ?>
            <!-- Reset Form -->
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru</label>
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        minlength="6"
                        placeholder="Minimal 6 karakter"
                        class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium text-gray-800"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password</label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        required 
                        minlength="6"
                        placeholder="Ketik ulang password"
                        class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium text-gray-800"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold shadow-lg transition transform hover:-translate-y-1"
                >
                    🔐 Reset Password
                </button>
            </form>
        <?php endif; ?>

        <!-- Back to Login -->
        <div class="text-center mt-6">
            <a href="<?= BASE_URL ?>/index.php?page=login" class="text-blue-600 hover:text-blue-700 font-semibold text-sm">
                ← Kembali ke Login
            </a>
        </div>
    </div>

    <!-- SweetAlert Error -->
    <?php if ($error && !empty($_POST)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= $error ?>',
                confirmButtonColor: '#2563EB'
            });
        </script>
    <?php endif; ?>

</body>
</html>
