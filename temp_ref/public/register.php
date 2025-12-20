<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

// Cek apakah yang akses adalah admin
if (!isset($_SESSION["user"]) || $_SESSION["user"]["role"] !== "admin") {
    die("Akses ditolak!");
}

// Jika form submit
if (isset($_POST["submit"])) {

    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role     = $_POST["role"];

    // Cek email sudah digunakan atau belum
    $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $error = "Email sudah digunakan!";
    } else {

        // Insert user baru
        $query = $pdo->prepare("
            INSERT INTO users (name, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        $query->execute([$name, $email, $password, $role]);

        header("Location: " . BASE_URL . "/views/admin/dashboard_admin.php?status=success");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register User</title>
</head>
<body>

<h2>Daftarkan User Baru</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form action="" method="POST">
    <input type="text" name="name" placeholder="Nama" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <select name="role" required>
        <option value="">-- Pilih Role --</option>
        <option value="admin">Admin</option>
        <option value="dosen">Dosen</option>
        <option value="mahasiswa">Mahasiswa</option>
    </select><br><br>

    <button type="submit" name="submit">Daftar</button>
</form>

</body>
</html>
