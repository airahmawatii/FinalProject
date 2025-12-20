<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskAcademia - Sistem Web Deadline Akademik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen text-white overflow-x-hidden selection:bg-blue-500 selection:text-white">

    <!-- Ambient Background -->
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[100px]"></div>
    </div>

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 border-b border-white/10 backdrop-blur-xl bg-slate-900/50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="<?= BASE_URL ?>/assets/img/logo_task_academia.jpg" class="w-10 h-10 rounded-full object-cover border border-white/10 shadow-lg" alt="Logo">
                <span class="text-xl font-bold tracking-wide">TaskAcademia</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="#features" class="hidden md:block text-sm font-medium text-blue-200 hover:text-white transition">Fitur Utama</a>
                <a href="<?= BASE_URL ?>/index.php?page=login" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-full font-bold transition shadow-lg shadow-blue-500/30 text-sm">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="container mx-auto px-6 py-20 lg:py-32 flex flex-col lg:flex-row items-center relative z-10">
        <div class="lg:w-1/2 lg:pr-12 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 border border-white/10 text-blue-300 text-sm font-bold mb-8 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                Official Academic Platform
            </div>
            <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight mb-6 tracking-tight">
                Deadline Terpantau, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">Nilai Aman.</span>
            </h1>
            <p class="text-lg lg:text-xl text-slate-300 mb-10 leading-relaxed max-w-lg mx-auto lg:mx-0">
                Satu platform terintegrasi untuk mengatur jadwal kuliah, pengingat tugas otomatis, dan monitoring akademik real-time.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="<?= BASE_URL ?>/index.php?page=login" class="px-8 py-4 bg-white text-blue-900 rounded-xl font-bold shadow-xl hover:bg-gray-100 transition transform hover:-translate-y-1">
                    Mulai Sekarang 🚀
                </a>
            </div>
        </div>
        
        <div class="lg:w-1/2 mt-16 lg:mt-0 relative group">
            <div class="absolute inset-0 bg-blue-500/30 rounded-3xl blur-[80px] group-hover:bg-blue-500/40 transition duration-700"></div>
            <!-- Glass Card Preview -->
            <div class="glass p-8 rounded-3xl relative animate-float">
                <div class="flex justify-between items-center mb-8 border-b border-white/10 pb-4">
                     <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                     </div>
                     <span class="text-xs text-slate-400 font-mono">dashboard_mahasiswa.php</span>
                </div>
                <!-- Mockup Content -->
                <div class="space-y-4">
                    <div class="h-8 w-2/3 bg-white/10 rounded-lg"></div>
                    <div class="h-32 w-full bg-white/5 rounded-xl border border-white/5 p-4 flex flex-col justify-between">
                         <div class="flex justify-between">
                            <div class="h-4 w-24 bg-blue-500/50 rounded"></div>
                            <div class="h-4 w-12 bg-red-500/50 rounded"></div>
                         </div>
                         <div class="h-2 w-full bg-slate-700 rounded-full mt-4 overflow-hidden">
                            <div class="h-full w-3/4 bg-blue-400 rounded-full"></div>
                         </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="h-24 bg-white/5 rounded-xl border border-white/5"></div>
                        <div class="h-24 bg-white/5 rounded-xl border border-white/5"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-24 relative z-10">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <h2 class="text-3xl lg:text-4xl font-bold mb-6">Kenapa TaskAcademia?</h2>
                <p class="text-slate-400 text-lg">Didukung fitur auto-sync yang membuat hidup mahasiswa lebih tenang.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/10 transition group">
                    <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center mb-6 text-blue-400 group-hover:scale-110 transition">
                       <span class="text-3xl">📅</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Google Calendar Sync</h3>
                    <p class="text-slate-400 leading-relaxed">Otomatis tambah jadwal ke kalender HP kamu. Nggak ada lagi alasan "lupa jadwal".</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/10 transition group">
                    <div class="w-14 h-14 bg-purple-500/20 rounded-2xl flex items-center justify-center mb-6 text-purple-400 group-hover:scale-110 transition">
                       <span class="text-3xl">🔔</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Reminder H-1</h3>
                    <p class="text-slate-400 leading-relaxed">Sistem akan "meneror" (secara halus) via email sehari sebelum deadline habis.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/10 transition group">
                    <div class="w-14 h-14 bg-green-500/20 rounded-2xl flex items-center justify-center mb-6 text-green-400 group-hover:scale-110 transition">
                       <span class="text-3xl">📊</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Monitoring Progres</h3>
                    <p class="text-slate-400 leading-relaxed">Dosen bisa intip siapa yang rajin submit awal dan siapa yang hobi mepet deadline.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-12 bg-slate-900/50 backdrop-blur-xl">
        <div class="container mx-auto px-6 text-center">
            <p class="text-slate-500 text-sm">© <?= date('Y') ?> TaskAcademia - Universitas Buana Perjuangan Karawang</p>
        </div>
    </footer>

    <script>
        // Simple float animation for the mockup
        document.querySelector('.animate-float').animate([
            { transform: 'translateY(0px)' },
            { transform: 'translateY(-20px)' },
            { transform: 'translateY(0px)' }
        ], {
            duration: 6000,
            iterations: Infinity,
            easing: 'ease-in-out'
        });
    </script>

</body>
</html>
