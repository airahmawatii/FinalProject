<?php
/**
 * UserModel - Jembatan Rahasia ke Database User
 * 
 * Class ini berisi semua perintah SQL untuk mengelola data pengguna,
 * mulai dari pendaftaran, update profil, hingga urusan token Google.
 */
class UserModel {
    private $pdo;

    // Menyiapkan koneksi database saat model ini dipanggil
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mencari user berdasarkan alamat email.
     * Digunakan saat proses Login untuk memverifikasi siapa yang masuk.
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Membuat User Baru.
     * Fungsi ini cerdas karena menggunakan "Transaction" (semua sukses atau tidak sama sekali)
     * untuk menyimpan data di tabel 'users' dan tabel detail peran ('mahasiswa' atau 'dosen').
     */
    public function create($nama, $email, $password, $role, $data = []) {
        try {
            // Mulai transaksi database
            $this->pdo->beginTransaction();

            // 1. Simpan data dasar ke tabel 'users'
            $status = $data['status'] ?? 'pending';
            $stmt = $this->pdo->prepare("
                INSERT INTO users (nama, email, password, role, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama, $email, $password, $role, $status]);
            $userId = $this->pdo->lastInsertId();

            // 2. Simpan data tambahan berdasarkan perannya
            if ($role === 'mahasiswa') {
                $nim = $data['nim'] ?? null;
                $prodi_id = $data['prodi_id'] ?? null;
                $angkatan_id = $data['angkatan_id'] ?? null;

                // Rahasia: Otomatis deteksi tahun angkatan dari 2 angka awal NIM
                if (!$angkatan_id && !empty($nim) && strlen($nim) >= 2) {
                    $tahun = "20" . substr($nim, 0, 2);
                    $stmtA = $this->pdo->prepare("SELECT id_angkatan FROM angkatan WHERE tahun = ?");
                    $stmtA->execute([$tahun]);
                    $a = $stmtA->fetch();
                    if ($a) $angkatan_id = $a['id_angkatan'];
                }

                $stmt = $this->pdo->prepare("
                    INSERT INTO mahasiswa (user_id, nim, prodi_id, angkatan_id)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $nim, $prodi_id, $angkatan_id]);
            } elseif ($role === 'dosen') {
                $nidn = $data['nidn'] ?? null;
                $nip = $data['nip'] ?? null;
                $stmt = $this->pdo->prepare("
                    INSERT INTO dosen (user_id, nidn, nip)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$userId, $nidn, $nip]);
            }

            // Jika semua lancar, simpan permanen
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Jika ada satu saja yang error, batalkan semua perubahan tadi
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Mengambil daftar semua user yang ada di sistem.
     */
    public function getAll() {
        return $this->pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil daftar user berdasarkan perannya (misal: ambil semua dosen saja).
     */
    public function getByRole($role) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role=?");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil data mata kuliah yang diajar oleh seorang dosen.
     */
    public function getDosenCourse($dosen_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM dosen_courses WHERE dosen_id = ?");
        $stmt->execute([$dosen_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil data lengkap user berdasarkan ID-nya.
     * Menggunakan JOIN agar data mahasiswa/dosen ikut terbawa.
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, 
                   m.nim, m.prodi_id, m.angkatan_id,
                   d.nidn, d.nip
            FROM users u
            LEFT JOIN mahasiswa m ON u.id = m.user_id
            LEFT JOIN dosen d ON u.id = d.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Memperbarui data user (Edit User).
     * Fungsi ini juga menggunakan "Transaction" untuk memastikan konsistensi data.
     */
    public function update($id, $nama, $email, $role, $data = []) {
        try {
            $this->pdo->beginTransaction();

            $status = $data['status'] ?? 'active';

            // 1. Update data inti di tabel users
            $stmt = $this->pdo->prepare("
                UPDATE users SET nama=?, email=?, role=?, status=? WHERE id=?
            ");
            $stmt->execute([$nama, $email, $role, $status, $id]);

            // 2. Update data spesifik per role (Mahasiswa/Dosen)
            if ($role === 'mahasiswa') {
                $nim = $data['nim'] ?? null;
                $prodi_id = $data['prodi_id'] ?? null;
                $angkatan_id = $data['angkatan_id'] ?? null;

                // Otomatis deteksi angkatan dari NIM jika tidak disediakan
                if (!$angkatan_id && !empty($nim) && strlen($nim) >= 2) {
                    $tahun = "20" . substr($nim, 0, 2);
                    $stmtA = $this->pdo->prepare("SELECT id_angkatan FROM angkatan WHERE tahun = ?");
                    $stmtA->execute([$tahun]);
                    $a = $stmtA->fetch();
                    if ($a) $angkatan_id = $a['id_angkatan'];
                }

                // Cek apakah data mahasiswa sudah ada
                $check = $this->pdo->prepare("SELECT user_id FROM mahasiswa WHERE user_id = ?");
                $check->execute([$id]);
                if ($check->fetch()) {
                    $stmt = $this->pdo->prepare("UPDATE mahasiswa SET nim=?, prodi_id=?, angkatan_id=? WHERE user_id=?");
                    $stmt->execute([$nim, $prodi_id, $angkatan_id, $id]);
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO mahasiswa (user_id, nim, prodi_id, angkatan_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$id, $nim, $prodi_id, $angkatan_id]);
                }
            } elseif ($role === 'dosen') {
                $nidn = $data['nidn'] ?? null;
                $nip = $data['nip'] ?? null;

                // Cek apakah data dosen sudah ada
                $check = $this->pdo->prepare("SELECT user_id FROM dosen WHERE user_id = ?");
                $check->execute([$id]);
                if ($check->fetch()) {
                    $stmt = $this->pdo->prepare("UPDATE dosen SET nidn=?, nip=? WHERE user_id=?");
                    $stmt->execute([$nidn, $nip, $id]);
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO dosen (user_id, nidn, nip) VALUES (?, ?, ?)");
                    $stmt->execute([$id, $nidn, $nip]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Menghapus user dari sistem secara permanen.
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    /**
     * Mengubah status user dari 'pending' menjadi 'active'.
     * Biasanya dilakukan oleh Admin saat menyetujui pendaftaran.
     */
    public function activateUser($id, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET status='active', role=? WHERE id=?");
        return $stmt->execute([$role, $id]);
    }

    /**
     * Mengambil daftar user yang baru mendaftar (masih butuh verifikasi).
     */
    public function getPendingUsers() {
        return $this->pdo->query("SELECT * FROM users WHERE status='pending'")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * MENYIMPAN TOKEN LOGIN GOOGLE.
     * Digunakan agar sistem tahu user ini login pakai Google apa.
     */
    public function updateGoogleTokens($id, $accessToken, $refreshToken, $expires) {
        if ($refreshToken) {
            $stmt = $this->pdo->prepare("UPDATE users SET access_token=?, refresh_token=?, token_expires=? WHERE id=?");
            return $stmt->execute([$accessToken, $refreshToken, $expires, $id]);
        } else {
            // Jika refresh token null (re-login biasa), jangan timpa refresh token lama yang mungkin masih valid
            $stmt = $this->pdo->prepare("UPDATE users SET access_token=?, token_expires=? WHERE id=?");
            return $stmt->execute([$accessToken, $expires, $id]);
        }
    }

    /**
     * MENYIMPAN TOKEN KALENDER GOOGLE.
     * Khusus untuk fitur sinkronisasi kalender agar tidak tertukar dengan token login.
     */
    public function updateGcalTokens($id, $accessToken, $refreshToken, $expires) {
        if ($refreshToken) {
            $stmt = $this->pdo->prepare("UPDATE users SET gcal_access_token=?, gcal_refresh_token=?, gcal_token_expires=? WHERE id=?");
            return $stmt->execute([$accessToken, $refreshToken, $expires, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET gcal_access_token=?, gcal_token_expires=? WHERE id=?");
            return $stmt->execute([$accessToken, $expires, $id]);
        }
    }

    /**
     * Memperbarui profil user (Nama, Password, atau Foto).
     */
    public function updateProfile($id, $nama, $password = null, $photo = null) {
        if ($password && $photo) {
            $stmt = $this->pdo->prepare("UPDATE users SET nama=?, password=?, photo=? WHERE id=?");
            return $stmt->execute([$nama, $password, $photo, $id]);
        } elseif ($password) {
            $stmt = $this->pdo->prepare("UPDATE users SET nama=?, password=? WHERE id=?");
            return $stmt->execute([$nama, $password, $id]);
        } elseif ($photo) {
            $stmt = $this->pdo->prepare("UPDATE users SET nama=?, photo=? WHERE id=?");
            return $stmt->execute([$nama, $photo, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET nama=? WHERE id=?");
            return $stmt->execute([$nama, $id]);
        }
    }

}
