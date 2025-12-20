<?php
// Prevent direct access to this file
if (!defined('BASE_URL')) {
    // If accessed directly, redirect to the main index
    header("Location: ../../index.php?page=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Akademik | TaskAcademy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }
        .pattern-grid {
            background-image: linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-4 md:p-8 overflow-x-hidden relative">

    <!-- Ambient Background Options -->
    <div class="fixed inset-0 pattern-grid pointer-events-none z-0"></div>
    <div class="fixed top-[-20%] left-[-10%] w-[600px] h-[600px] bg-blue-600/30 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-600/30 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="w-full max-w-6xl z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
        
        <!-- Left Side: Branding (Visible on Mobile now too) -->
        <div class="text-white text-center lg:text-left space-y-6 order-1 lg:order-none">
            <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-full mb-4 lg:mb-0">
                <img src="<?= BASE_URL ?>/assets/img/logo_task_academia.jpg" alt="TaskAcademia Logo" class="w-8 h-8 rounded-lg object-cover">
                <span class="font-bold tracking-wide">TaskAcademia</span>
            </div>

            <h1 class="text-4xl lg:text-6xl font-extrabold leading-tight">
                Sistem Deadline Akademik <br>
            </h1>
            
            <p class="text-blue-200 text-lg lg:text-xl max-w-xl mx-auto lg:mx-0 leading-relaxed">
                Kelola tugas, pantau deadline, dan monitoring progress akademik.
            </p>

            <div class="hidden lg:flex gap-8 pt-4">
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10 flex items-center gap-4">
                    <div class="p-3 bg-blue-500/20 rounded-xl">📅</div>
                    <div class="text-left">
                        <p class="font-bold text-xl">Auto-Sync</p>
                        <p class="text-xs text-blue-300">Google Calendar</p>
                    </div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10 flex items-center gap-4">
                    <div class="p-3 bg-purple-500/20 rounded-xl">🔔</div>
                    <div class="text-left">
                        <p class="font-bold text-xl">Real-time</p>
                        <p class="text-xs text-blue-300">Notifications</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Card -->
        <div class="order-2 lg:order-none w-full max-w-md mx-auto" x-data="{ showPassword: false }">
            <div class="glass rounded-3xl p-8 md:p-10 relative overflow-hidden">
                <!-- Highlight Line -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-purple-500"></div>

                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-bold text-gray-800">Selamat Datang!</h2>
                    <p class="text-gray-500 text-sm mt-2">Silakan login untuk melanjutkan.</p>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/index.php" class="space-y-5">
                    <input type="hidden" name="action" value="login">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                            </div>
                            <input type="email" name="email" required 
                                   class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none focus:bg-white transition font-medium text-gray-800 placeholder-gray-400 shadow-inner" 
                                   placeholder="email@example.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" name="password" required 
                                   class="block w-full pl-12 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none focus:bg-white transition font-medium text-gray-800 placeholder-gray-400 shadow-inner" 
                                   placeholder="••••••••">
                            
                            <!-- Toggle Password Visibility -->
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.574-2.59M5.316 5.316l13.368 13.368M9.542 9.542a3 3 0 014.242 4.242"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-right">
                        <a href="forgot_password.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                            Lupa Password?
                        </a>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-lg shadow-blue-500/30 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition transform hover:-translate-y-0.5">
                        Masuk
                    </button>
                </form>

                <div class="my-6 flex items-center">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Atau</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                <a href="<?= BASE_URL ?>/index.php?action=google_login" class="w-full flex items-center justify-center px-4 py-3 border-2 border-gray-100 rounded-xl hover:bg-gray-50 hover:border-gray-200 transition gap-3 group">
                    <svg class="h-5 w-5 group-hover:scale-110 transition" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span class="text-sm font-bold text-gray-700">Masuk dengan Google</span>
                </a>
                
                <!-- Admin Link Hidden for Security -->
                <!-- Access via ?page=admin_login -->

            </div>
        </div>
    </div>

    <!-- SweetAlert Logic -->
    <?php if (isset($error) && !empty($error)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal Masuk',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#2563EB',
            confirmButtonText: 'Coba Lagi',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold'
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>