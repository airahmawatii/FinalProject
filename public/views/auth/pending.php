<?php require_once __DIR__ . '/../../../app/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Verifikasi | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-3xl shadow-2xl p-10 max-w-lg text-center">
        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
            <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Menunggu Verifikasi Admin</h1>
        <p class="text-gray-500 mb-8">
            Akun Anda berhasil dibuat, namun memerlukan persetujuan Administrator sebelum dapat digunakan.
            <br><br>
            Silakan hubungi Admin Kampus atau cek kembali nanti.
        </p>

        <a href="<?= BASE_URL ?>/logout.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition shadow-lg">
            Logout & Kembali
        </a>
    </div>

</body>
</html>
