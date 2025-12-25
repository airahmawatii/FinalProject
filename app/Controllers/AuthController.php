<?php
/**
 * AuthController - Pengatur Keamanan & Login
 * 
 * Class ini bertanggung jawab mengurus segala sesuatu yang berkaitan dengan 
 * pintu masuk (Login), pendaftaran (Register via Google), dan hak akses (Role).
 */

require_once __DIR__ . "/../Models/UserModel.php";

class AuthController
{
    private $model;

    // Menghubungkan controller dengan model User saat aplikasi dijalankan
    public function __construct($pdo)
    {
        $this->model = new UserModel($pdo);
    }

    /**
     * Login Manual (Email & Password)
     * Menggunakan form login yang diisi sendiri oleh user.
     */
    public function login()
    {
        $error = "";

        // Jika user menekan tombol login (mengirim data POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $pw    = $_POST['password'];

            // Cari user di database berdasarkan email
            $user = $this->model->findByEmail($email);

            // Verifikasi kecocokan password yang di-hash
            if ($user && password_verify($pw, $user['password'])) {

                // CEK STATUS AKUN: Jika masih 'pending', lempar ke halaman tunggu
                if ($user['status'] === 'pending') {
                     $_SESSION['user'] = $user;
                     header("Location: " . BASE_URL . "/views/auth/pending.php");
                     exit;
                }

                // Jika akun 'active', simpan data user ke dalam Session (ingatan server)
                $_SESSION['user'] = $user;
                $_SESSION['flash_message'] = "Selamat datang kembali, " . $user['nama'] . "!";

                // Arahkan ke dashboard sesuai jabatan (Role)
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
                // Jika password salah atau email tidak ditemukan
                $error = "Email atau password salah!";
            }
        }

        // Tampilkan halaman login (View)
        include __DIR__ . "/../../public/views/auth/login_view.php";
    }

    /**
     * Google Login - Langkah 1 (Inisialisasi)
     * Membuat link khusus agar user diarahkan ke halaman login Google.
     */
    public function googleLogin()
    {
        require_once __DIR__ . '/../Services/GoogleClientService.php';
        // Gunakan Google Service (Mode OAuth dipaksa aktif untuk login)
        $service = new GoogleClientService(true); 
        $client = $service->getClient();
        
        // Buat URL sakti dari Google dan arahkan user ke sana
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Google Callback - Langkah 2 (Setelah pilih akun)
     * Menangani data yang dikirim balik oleh Google setelah user sukses login.
     */
    public function googleCallback()
    {
        require_once __DIR__ . '/../Services/GoogleClientService.php';
        $service = new GoogleClientService(true);
        $client = $service->getClient();

        // Jika Google mengirimkan 'code' rahasia
        if (isset($_GET['code'])) {
            try {
                // Tukarkan 'code' menjadi Token Akses
                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                if (isset($token['error'])) {
                    throw new Exception("Google Auth Error: " . $token['error']);
                }
                $client->setAccessToken($token);

                // Ambil data profil (Email & Nama) dari akun Google tersebut
                $google_oauth = new Google\Service\Oauth2($client);
                $google_account_info = $google_oauth->userinfo->get();
                $email = $google_account_info->email;
                $name = $google_account_info->name;

                // --- VALIDASI DOMAIN KAMPUS ---
                // Mengatur Role otomatis berdasarkan domain email universitas
                $domain = substr(strrchr($email, "@"), 1);
                $role = '';

                if ($domain === 'mhs.ubpkarawang.ac.id') {
                    $role = 'mahasiswa';
                } elseif ($domain === 'ubpkarawang.ac.id') {
                    $role = 'dosen';
                } elseif ($email === 'naylanafizah2@gmail.com') { 
                    // Pengecualian khusus untuk developer/testing
                    $role = 'dosen';
                } else {
                    // Jika domain lain (misal @gmail.com), default sementara ke mahasiswa
                    $role = 'mahasiswa';
                }

                // Cek apakah email ini sudah pernah terdaftar di database kita?
                $user = $this->model->findByEmail($email);
                
                // Ambil Token Google untuk keperluan sinkronisasi Kalender nanti
                $access_token = $token['access_token'];
                $refresh_token = $token['refresh_token'] ?? null;
                $token_expires = time() + $token['expires_in'];
                
                if (!$user) {
                    // 1. PENDAFTARAN BARU (Otomatis status 'pending')
                    $random_password = bin2hex(random_bytes(8));
                    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
                    
                    // Simpan user baru ke database
                    if ($this->model->create($name, $email, $hashed_password, $role)) {
                        $user = $this->model->findByEmail($email);
                        // Simpan Kunci Google ke database (untuk login & sinkron kalender)
                        $this->model->updateGoogleTokens($user['id'], $access_token, $refresh_token, $token_expires);
                        $this->model->updateGcalTokens($user['id'], $access_token, $refresh_token, $token_expires);
                    } else {
                        throw new Exception("Gagal mendaftarkan user baru.");
                    }
                } else {
                    // 2. UPDATE USER LAMA
                    // Tetap update token terbaru setiap kali login agar sinkronisasi kalender lancar
                    $this->model->updateGoogleTokens($user['id'], $access_token, $refresh_token, $token_expires);
                    $this->model->updateGcalTokens($user['id'], $access_token, $refresh_token, $token_expires);
                    
                    // Ambil data user terbaru setelah update
                    $user = $this->model->findByEmail($email);
                }
                
                // --- PROSES PENGARAHAN AKHIR ---
                $_SESSION['user'] = $user;

                // Jika status 'pending', dilarang masuk Dashboard
                if ($user['status'] === 'pending') {
                    header("Location: " . BASE_URL . "/views/auth/pending.php");
                    exit;
                }

                // Jika status 'active', tampilkan salam dan masuk Dashboard
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
                // Tampilkan error jika terjadi kegagalan sistem Google
                $error = "Login Gagal: " . $e->getMessage();
                include __DIR__ . "/../../public/views/auth/login_view.php";
            }
        }
    }
}