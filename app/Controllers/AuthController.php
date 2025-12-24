<?php
require_once __DIR__ . "/../Models/UserModel.php";

class AuthController
{
    private $model;

    public function __construct($pdo)
    {
        $this->model = new UserModel($pdo);
    }

    public function login()
    {
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $pw    = $_POST['password'];

            $user = $this->model->findByEmail($email);

            if ($user && password_verify($pw, $user['password'])) {

                $_SESSION['user'] = $user;
                $_SESSION['flash_message'] = "Selamat datang kembali, " . $user['nama'] . "!";

                if ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "/views/admin/dashboard_admin.php");
                    exit;
                } elseif ($user['role'] === 'dosen') {
                    header("Location: " . BASE_URL . "/views/dosen/dashboard.php");
                    exit;
                } else {
                    header("Location: " . BASE_URL . "/views/mahasiswa/dashboard_mahasiswa.php");
                    exit;
                }
            } else {
                $error = "Email atau password salah!";
            }
        }

        include __DIR__ . "/../../public/views/auth/login_view.php";
    }

    public function googleLogin()
    {
        require_once __DIR__ . '/../Services/GoogleClientService.php';
        $service = new GoogleClientService(true); // Force OAuth for Login
        $client = $service->getClient();
        
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    public function googleCallback()
    {
        require_once __DIR__ . '/../Services/GoogleClientService.php';
        $service = new GoogleClientService(true); // Force OAuth for Login
        $client = $service->getClient();

        if (isset($_GET['code'])) {
            try {
                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                if (isset($token['error'])) {
                    throw new Exception("Google Auth Error: " . $token['error']);
                }
                $client->setAccessToken($token);

                $google_oauth = new Google_Service_Oauth2($client);
                $google_account_info = $google_oauth->userinfo->get();
                $email = $google_account_info->email;
                $name = $google_account_info->name;

                // --- VALIDASI DOMAIN KAMPUS ---
                // Kita cek domain emailnya untuk menentukan ROLE otomatis.
                // - @mhs.ubpkarawang.ac.id -> Mahasiswa
                // - @ubpkarawang.ac.id -> Dosen
                $domain = substr(strrchr($email, "@"), 1);
                $role = '';

                if ($domain === 'mhs.ubpkarawang.ac.id') {
                    $role = 'mahasiswa';
                } elseif ($domain === 'ubpkarawang.ac.id') {
                    $role = 'dosen';
                } elseif ($email === 'naylanafizah2@gmail.com') { // DEV/TESTING MODE
                    // Ganti 'dosen' jadi 'admin' atau 'mahasiswa' sesuai kebutuhan testing kamu
                    $role = 'dosen';
                } else {
                    // Invalid domain
                    // TEMPORARY: Allow all emails for testing
                    // $error = "Email allow only for @mhs.ubpkarawang.ac.id or @ubpkarawang.ac.id";
                    // include __DIR__ . "/../../public/views/auth/login_view.php";
                    // return;
                    $role = 'mahasiswa';
                }

                // Check if user exists
                $user = $this->model->findByEmail($email);
                
                // === TOKEN HANDLING START ===
                $access_token = $token['access_token'];
                $refresh_token = $token['refresh_token'] ?? null; // Only provided on first consent or if prompt=consent
                $expires_in = $token['expires_in'];
                $token_expires = time() + $expires_in;
                
                if (!$user) {
                    // Register new user
                    // Generate random password
                    $random_password = bin2hex(random_bytes(8));
                    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
                    
                    // Buat user baru di database
                    if ($this->model->create($name, $email, $hashed_password, $role)) {
                        $user = $this->model->findByEmail($email);
                        // Simpan Token Google (Penting untuk Calendar Sync!)
                        // Access Token & Refresh Token disimpan agar "Robot" bisa akses kalender nanti.
                        $this->model->updateGoogleTokens($user['id'], $access_token, $refresh_token, $token_expires);
                    } else {
                        $error = "Gagal mendaftarkan user baru.";
                        include __DIR__ . "/../../public/views/auth/login_view.php";
                        return;
                    }
                } else {
                    // Update existing user tokens
                    $this->model->updateGoogleTokens($user['id'], $access_token, $refresh_token, $token_expires);
                    
                    // Refresh user data
                    $user = $this->model->findByEmail($email);
                }
                // === TOKEN HANDLING END ===
                
                // Check verification status
                if ($user['status'] === 'pending') {
                    $_SESSION['user'] = $user; // Create session to show name if needed
                    header("Location: " . BASE_URL . "/views/auth/pending.php");
                    exit;
                }

                // Login Active Users
                $_SESSION['user'] = $user;
                $_SESSION['flash_message'] = "Login Google Berhasil! Hai, " . $user['nama'];

                if ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "/views/admin/dashboard_admin.php");
                } elseif ($user['role'] === 'dosen') {
                    header("Location: " . BASE_URL . "/views/dosen/dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "/views/mahasiswa/dashboard_mahasiswa.php");
                }
                exit;
            } catch (Exception $e) {
                $error = "Login Gagal: " . $e->getMessage();
                include __DIR__ . "/../../public/views/auth/login_view.php";
            }
        }
    }
}