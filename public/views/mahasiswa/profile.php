<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mahasiswa') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/config.php';

require_once __DIR__ . '/../../../app/config/database.php';
require_once __DIR__ . '/../../../app/Models/UserModel.php';

$db = new Database(); 
$pdo = $db->connect();
$userModel = new UserModel($pdo);

$role = $_SESSION['user']['role'];
$success = "";
$error = "";

// Form Handling (Code preserved)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $pw = $_POST['password'] ?? '';
    // ... (Photo logic preserved) ...
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
         $uploadDir = __DIR__ . '/../../uploads/profiles/';
         if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
         $newFilename = "profile_" . $_SESSION['user']['id'] . "_" . time() . "." . strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
         if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFilename)) $photo = $newFilename;
    }
    $hashedPw = $pw ? password_hash($pw, PASSWORD_DEFAULT) : null;
    $userModel->updateProfile($_SESSION['user']['id'], $name, $hashedPw, $photo);
    $_SESSION['user'] = $userModel->findById($_SESSION['user']['id']);
    $success = "Profil berhasil diperbarui!";
}

$user = $_SESSION['user'];
$photoUrl = !empty($user['photo']) ? BASE_URL . "/uploads/profiles/" . $user['photo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=random";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .sidebar { background: rgba(15, 23, 42, 0.95); }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-blue-900 to-slate-900 min-h-screen flex text-gray-800">

<<<<<<< HEAD
    <!-- Main Content (Full Page) -->
    <main class="w-full min-h-screen flex flex-col items-center justify-center p-4 md:p-8 relative">
=======
    <!-- Sidebar Integrated -->
    <?php include __DIR__ . '/../layouts/sidebar_mahasiswa.php'; ?>

    <!-- Main Content -->
    <main id="main-content" class="flex-1 relative overflow-y-auto w-full md:w-auto transition-all duration-300 md:ml-72">

>>>>>>> d18683958109ae9fe0244a71fdc030651f124058
        
        <!-- Background Orbs -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[10%] right-[10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen"></div>
        </div>

<<<<<<< HEAD
        <div class="w-full max-w-3xl relative z-10">
=======
        <div class="p-6 md:p-10 relative z-10 max-w-4xl mx-auto pt-20 md:pt-10">
             <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2 text-white">Edit Profil</h1>
                <p class="text-blue-200">Perbarui informasi akun Anda.</p>
            </div>

>>>>>>> d18683958109ae9fe0244a71fdc030651f124058
            <!-- Profile Form Card -->
            <div class="glass rounded-3xl p-8 md:p-12 shadow-2xl">
                 <?php if ($success): ?>
                    <script>
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= $success ?>', timer: 1500, showConfirmButton: false });
                    </script>
                <?php endif; ?>

<<<<<<< HEAD
                <!-- Header with Back Button (Inside Card) -->
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-400 hover:text-blue-600 mb-2 transition text-sm font-semibold group">
                        <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        Kembali
                    </a>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Profil</h1>
                    <p class="text-gray-500 mt-1">Perbarui informasi akun Anda.</p>
                </div>

=======
>>>>>>> d18683958109ae9fe0244a71fdc030651f124058
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="flex items-center gap-6 mb-8 p-6 bg-blue-50/50 rounded-2xl border border-blue-100">
                        <img src="<?= $photoUrl ?>" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Ganti Foto Profil</label>
                            <input type="file" name="photo" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['nama']) ?>" required class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none font-semibold text-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="w-full px-5 py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-500 font-medium cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru <span class="text-gray-400 font-normal">(Opsional, isi jika ingin ganti)</span></label>
                        <input type="password" name="password" placeholder="••••••" class="w-full px-5 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

<<<<<<< HEAD
                    <div class="pt-4 flex gap-4">
                        <a href="javascript:history.back()" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-4 rounded-xl font-bold transition text-center">Batal</a>
                        <button type="submit" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1">
=======
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1">
>>>>>>> d18683958109ae9fe0244a71fdc030651f124058
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
