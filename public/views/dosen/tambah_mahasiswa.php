<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../app/config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'dosen') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/../../../app/config/database.php';

$db = new Database();
$pdo = $db->connect();

$dosen_id = $_SESSION['user']['id'];
$message = '';
$error = '';

// Get courses taught by this dosen
$courses = [];
try {
    $sql = "SELECT DISTINCT c.id, c.name 
            FROM courses c
            JOIN dosen_courses dc ON dc.matkul_id = c.id
            WHERE dc.dosen_id = :dosen_id
            ORDER BY c.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['dosen_id' => $dosen_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading courses: " . $e->getMessage();
}

// Get all students
$students = [];
try {
    $sql = "SELECT u.id, u.nama, m.nim, a.tahun as angkatan
            FROM users u
            LEFT JOIN mahasiswa m ON u.id = m.user_id
            LEFT JOIN angkatan a ON m.angkatan_id = a.id_angkatan
            WHERE u.role = 'mahasiswa'
            ORDER BY u.nama ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading students: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    
    if ($course_id && $student_id) {
        try {
            // Check if student is already enrolled
            $check = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
            $check->execute([$student_id, $course_id]);
            
            if ($check->fetchColumn() > 0) {
                $error = "Mahasiswa sudah terdaftar di mata kuliah ini!";
            } else {
                // Insert enrollment
                $insert = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                $insert->execute([$student_id, $course_id]);
                $message = "Mahasiswa berhasil ditambahkan!";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Silakan pilih mata kuliah dan mahasiswa!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa | TaskAcademia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Outfit', sans-serif; } 
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen p-8 flex items-center justify-center font-outfit">

    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] mix-blend-screen"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] mix-blend-screen"></div>
    </div>

    <div class="w-full max-w-2xl backdrop-blur-2xl bg-slate-900/80 border border-white/20 p-10 rounded-[2.5rem] shadow-2xl relative z-10 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-cyan-500"></div>

        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Tambah Mahasiswa</h2>
                <p class="text-blue-300/60 text-sm mt-1">Daftarkan mahasiswa ke mata kuliah Anda.</p>
            </div>
            <a href="lihat_mahasiswa.php" class="w-10 h-10 rounded-xl backdrop-blur-2xl bg-slate-900/80 border border-white/20 flex items-center justify-center text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all group">
                <span class="group-hover:rotate-90 transition-transform duration-300">✕</span>
            </a>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-500/10 text-green-300 p-4 rounded-2xl mb-8 text-sm border border-green-500/20 flex items-center gap-3">
                <span class="text-xl">✓</span> <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-500/10 text-red-300 p-4 rounded-2xl mb-8 text-sm border border-red-500/20 flex items-center gap-3">
                <span class="text-xl">⚠️</span> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Pilih Mata Kuliah</label>
                <div class="relative">
                    <select name="course_id" required 
                            class="w-full px-5 py-3.5 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 backdrop-blur-sm border border-white/10 appearance-none cursor-pointer">
                        <option value="">-- Pilih Mata Kuliah --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-extrabold text-blue-300 uppercase tracking-widest ml-1">Pilih Mahasiswa</label>
                <div class="relative">
                    <select name="student_id" required id="studentSelect"
                            class="w-full px-5 py-3.5 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:outline-none text-white bg-slate-800/80 backdrop-blur-sm border border-white/10 appearance-none cursor-pointer">
                        <option value="">-- Pilih Mahasiswa --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= htmlspecialchars($student['nama']) ?> 
                                <?php if ($student['nim']): ?>
                                    (<?= htmlspecialchars($student['nim']) ?>)
                                <?php endif; ?>
                                <?php if ($student['angkatan']): ?>
                                    - Angkatan <?= htmlspecialchars($student['angkatan']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                <p class="text-xs text-blue-300/50 ml-1 mt-2">💡 Pilih mahasiswa yang ingin ditambahkan, termasuk yang tidak lulus semester.</p>
            </div>

            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-4 rounded-2xl font-bold shadow-xl shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98] border border-white/10">
                    ➕ Tambahkan Mahasiswa
                </button>
                <a href="lihat_mahasiswa.php" class="px-8 py-4 rounded-2xl backdrop-blur-2xl bg-slate-900/80 border border-white/20 text-slate-300 hover:bg-white/20 font-bold transition flex items-center">
                    Batalkan
                </a>
            </div>
        </form>

        <?php if (!empty($courses)): ?>
        <div class="mt-8 p-4 bg-blue-500/5 border border-blue-500/20 rounded-2xl">
            <p class="text-xs text-blue-300/70">
                <span class="font-bold">📚 Info:</span> Anda mengajar <?= count($courses) ?> mata kuliah. Mahasiswa yang ditambahkan akan otomatis terdaftar di mata kuliah yang dipilih.
            </p>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
