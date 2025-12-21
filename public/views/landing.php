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
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .hero-text-shadow {
            text-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body class="min-h-screen text-white overflow-x-hidden selection:bg-blue-500 selection:text-white">

    <!-- HD Background Image with Overlay -->
    <div class="fixed inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1920&auto=format&fit=crop" 
             alt="Background" 
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-[2px]"></div>
        <!-- Gradient Mesh Overlay for Depth -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-slate-900/60"></div>
    </div>

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 border-b border-white/5 backdrop-blur-md bg-slate-900/30">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <!-- Logo with white rounded container for visibility -->
                <div class="bg-white/90 p-1.5 rounded-xl shadow-lg shadow-blue-500/20">
                     <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="TaskAcademia" class="h-8 w-auto">
                </div>
                <span class="font-bold text-xl tracking-tight text-white/90">TaskAcademia</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="#features" class="hidden md:block text-sm font-semibold text-slate-300 hover:text-white transition">Fitur Utama</a>
                <a href="<?= BASE_URL ?>/index.php?page=login" class="bg-white text-slate-900 hover:bg-blue-50 px-6 py-2.5 rounded-full font-bold transition shadow-lg shadow-white/10 text-sm flex items-center gap-2 transform hover:scale-105 active:scale-95">
                    Login
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="container mx-auto px-6 py-24 lg:py-36 flex flex-col lg:flex-row items-center relative z-10">
        
        <!-- Text Content -->
        <div class="lg:w-1/2 lg:pr-12 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold mb-8 backdrop-blur-md uppercase tracking-wide">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse shadow-[0_0_10px_rgba(96,165,250,0.7)]"></span>
                Official Academic Platform
            </div>
            
            <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight mb-6 tracking-tight hero-text-shadow">
                Deadline Aman, <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-cyan-200 to-white">IPK Nyaman.</span>
            </h1>
            
            <p class="text-lg lg:text-xl text-slate-200 mb-10 leading-relaxed max-w-xl mx-auto lg:mx-0 font-medium opacity-90">
                Satu platform terintegrasi untuk mahasiswa modern. Sinkronisasi jadwal, pengingat tugas otomatis, dan monitoring akademik tanpa ribet.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="<?= BASE_URL ?>/index.php?page=login" class="px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-bold shadow-xl shadow-blue-600/30 hover:shadow-blue-600/50 transition transform hover:-translate-y-1 flex justify-center items-center gap-2 border border-blue-400/20">
                    <span>Mulai Sekarang</span> 🚀
                </a>
                <a href="#features" class="px-8 py-4 glass text-white rounded-2xl font-bold hover:bg-white/20 transition transform hover:-translate-y-1 flex justify-center items-center">
                    Pelajari Fitur
                </a>
            </div>
            
            <!-- Trust Badges -->
            <div class="mt-12 flex items-center justify-center lg:justify-start gap-6 opacity-70 grayscale hover:grayscale-0 transition duration-500">
                 <!-- Placeholder text for university/tech stack -->
                 <div class="text-xs font-bold text-slate-400 uppercase tracking-widest border-t border-white/20 pt-4 w-full">
                    Powered by TaskAcademia
                 </div>
            </div>
        </div>
        
        <!-- Hero Visual / Mockup -->
        <div class="lg:w-1/2 mt-20 lg:mt-0 relative group perspective-1000">
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-blue-500/30 rounded-full blur-[100px] group-hover:bg-blue-500/40 transition duration-1000"></div>
            
            <!-- Glass Card Floating -->
            <div class="glass p-8 rounded-3xl relative animate-float transform rotate-y-6 group-hover:rotate-y-0 transition duration-700 ease-out">
                <!-- Mac Traffic Lights -->
                <div class="flex items-center gap-2 mb-6 border-b border-white/10 pb-4">
                    <div class="w-3 h-3 rounded-full bg-red-400 shadow-sm"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400 shadow-sm"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400 shadow-sm"></div>
                    <div class="ml-4 px-3 py-1 bg-black/20 rounded-md text-[10px] font-mono text-slate-400">task_manager_v2.0</div>
                </div>
                
                <!-- Mock Interface -->
                <div class="space-y-5">
                    <!-- Header Mock -->
                    <div class="flex justify-between items-center">
                         <div class="h-10 w-32 bg-white/10 rounded-xl"></div>
                         <div class="h-10 w-10 bg-white/20 rounded-full"></div>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="h-28 bg-gradient-to-br from-blue-500/30 to-blue-600/10 rounded-2xl border border-blue-400/20 p-4">
                             <div class="w-8 h-8 bg-blue-500 rounded-lg mb-2"></div>
                             <div class="h-3 w-16 bg-white/20 rounded mb-1"></div>
                             <div class="h-6 w-8 bg-white/40 rounded"></div>
                        </div>
                        <div class="h-28 bg-white/5 rounded-2xl border border-white/10 p-4">
                             <div class="w-8 h-8 bg-purple-500/50 rounded-lg mb-2"></div>
                             <div class="h-3 w-16 bg-white/20 rounded mb-1"></div>
                        </div>
                    </div>
                    
                    <!-- List Items -->
                    <div class="space-y-3">
                         <div class="h-16 bg-white/5 rounded-xl border border-white/10 flex items-center px-4 gap-3">
                             <div class="w-4 h-4 rounded border-2 border-slate-500"></div>
                             <div class="flex-1 h-3 bg-white/10 rounded"></div>
                         </div>
                         <div class="h-16 bg-white/5 rounded-xl border border-white/10 flex items-center px-4 gap-3">
                             <div class="w-4 h-4 rounded border-2 border-green-500 bg-green-500"></div>
                             <div class="flex-1 h-3 bg-white/10 rounded w-1/2 opacity-50 line-through"></div>
                         </div>
                    </div>
                </div>
                
                <!-- Floating Notification Badge -->
                <div class="absolute -right-6 top-20 bg-white text-slate-900 p-4 rounded-2xl shadow-2xl animate-bounce-slow flex items-center gap-3 max-w-[200px]">
                    <div class="bg-red-100 p-2 rounded-full text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold">Pengingat Tugas</p>
                        <p class="text-[10px] text-slate-500">Besok deadline PWEB!</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-24 relative z-10 bg-slate-900/40 backdrop-blur-sm border-t border-white/5">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <h2 class="text-3xl lg:text-4xl font-bold mb-6 text-white">Kenapa TaskAcademia?</h2>
                <p class="text-slate-300 text-lg">Platform pintar yang mengerti kebutuhan mahasiswa sibuk seperti kamu.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/10 transition group hover:-translate-y-2 duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition">
                       <span class="text-3xl">📅</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Google Calendar Sync</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Otomatis sinkronisasi jadwal kuliah dan tugas ke smartphone kamu. Anti lupa, anti skip.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/10 transition group hover:-translate-y-2 duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-400 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-purple-500/30 group-hover:scale-110 transition">
                       <span class="text-3xl">🔔</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Smart Reminder</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Notifikasi H-1 via Email yang memastikan kamu sadar deadline sebelum terlambat.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass p-8 rounded-3xl hover:bg-white/10 transition group hover:-translate-y-2 duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-400 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-green-500/30 group-hover:scale-110 transition">
                       <span class="text-3xl">🚀</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Monitoring Real-time</h3>
                    <p class="text-slate-400 leading-relaxed text-sm">Dashboard interaktif untuk melihat progres tugas, status submit, dan feedback dosen.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-8 bg-black/40 backdrop-blur-xl relative z-10">
        <div class="container mx-auto px-6 text-center">
            <p class="text-slate-500 text-sm font-medium">© <?= date('Y') ?> TaskAcademia - Universitas Buana Perjuangan Karawang</p>
        </div>
    </footer>

    <style>
        .perspective-1000 { perspective: 1000px; }
        .rotate-y-6 { transform: rotateY(-10deg) rotateX(5deg); }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotateY(-10deg) rotateX(5deg); }
            50% { transform: translateY(-15px) rotateY(-10deg) rotateX(5deg); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-bounce-slow { animation: bounce-slow 3s infinite ease-in-out; }
    </style>

</body>
</html>