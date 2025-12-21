<?php
session_start();
require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/Notification.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Email wajib diisi.";
    } else {
        try {
            $db = new Database();
            $pdo = $db->connect();
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, nama FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                
                // Delete old tokens for this email
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);
                
                // Insert new token
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
                $stmt->execute([$email, $token]);
                
                // Send email
                $resetLink = "http://localhost/FinalProject/public/views/auth/reset_password.php?token=$token";
                
                $emailBody = "
                    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;'>
                        <!-- Header -->
                        <div style='background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 40px 30px; text-align: center;'>
                            <h1 style='color: white; margin: 0; font-size: 24px; font-weight: 800;'>Reset Password 🔐</h1>
                            <p style='color: #bfdbfe; margin-top: 5px; font-size: 14px;'>TaskAcademy</p>
                        </div>

                        <!-- Content -->
                        <div style='padding: 30px; background: #ffffff;'>
                            <p style='color: #334155; font-size: 16px; line-height: 1.6;'>
                                Halo <strong>{$user['nama']}</strong>,<br><br>
                                Kami menerima permintaan untuk reset password akun Anda. Klik tombol di bawah untuk membuat password baru:
                            </p>

                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='$resetLink' style='background-color: #2563EB; color: white; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);'>
                                    Reset Password
                                </a>
                            </div>

                            <div style='background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                <p style='margin: 0; color: #92400e; font-size: 14px;'>
                                    ⚠️ <strong>Link ini hanya berlaku selama 1 jam.</strong><br>
                                    Jika Anda tidak meminta reset password, abaikan email ini.
                                </p>
                            </div>

                            <p style='color: #64748b; font-size: 13px; margin-top: 20px;'>
                                Atau copy link berikut ke browser Anda:<br>
                                <code style='background: #f1f5f9; padding: 5px 10px; border-radius: 4px; font-size: 12px; word-break: break-all;'>$resetLink</code>
                            </p>
                        </div>
                        
                        <!-- Footer -->
                        <div style='background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8;'>
                            &copy; " . date('Y') . " TaskAcademy - Universitas Buana Perjuangan Karawang
                        </div>
                    </div>
                ";
                
                $notifier = new Notification();
                $sent = $notifier->send($email, "Reset Password - TaskAcademy", $emailBody, "TaskAcademy");
                
                if ($sent) {
                    $success = "Link reset password telah dikirim ke email Anda. Silakan cek inbox/spam.";
                } else {
                    $error = "Gagal mengirim email. Silakan coba lagi.";
                }
            } else {
                // Don't reveal if email exists or not (security)
                $success = "Jika email terdaftar, link reset password akan dikirim.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
            error_log("Forgot Password Error: " . $e->getMessage());
        }
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
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔐 Lupa Password</h1>
            <p class="text-gray-600">Masukkan email Anda untuk reset password</p>
        </div>

        <!-- Form -->
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    placeholder="email@example.com"
                    class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium text-gray-800"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold shadow-lg transition transform hover:-translate-y-1"
            >
                📧 Kirim Link Reset
            </button>
        </form>

        <!-- Back to Login -->
        <div class="text-center mt-6">
            <a href="login_view.php" class="text-blue-600 hover:text-blue-700 font-semibold text-sm">
                ← Kembali ke Login
            </a>
        </div>
    </div>

    <!-- SweetAlert Messages -->
    <?php if ($success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= $success ?>',
                confirmButtonColor: '#2563EB'
            });
        </script>
    <?php endif; ?>

    <?php if ($error): ?>
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
