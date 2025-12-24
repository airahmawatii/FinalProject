<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Cari user berdasarkan Email (Untuk Login)
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nama, $email, $password, $role, $data = []) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO users (nama, email, password, role, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$nama, $email, $password, $role]);
            $userId = $this->pdo->lastInsertId();

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

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    }

    // This method was missing and caused the fatal error
    public function getByRole($role) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role=?");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDosenCourse($dosen_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM dosen_courses WHERE dosen_id = ?");
        $stmt->execute([$dosen_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    public function update($id, $nama, $email, $role, $data = []) {
        try {
            $this->pdo->beginTransaction();

            $status = $data['status'] ?? 'active';

            // 1. Update dasar di tabel users
            $stmt = $this->pdo->prepare("
                UPDATE users SET nama=?, email=?, role=?, status=? WHERE id=?
            ");
            $stmt->execute([$nama, $email, $role, $status, $id]);

            // 2. Update data spesifik berdasarkan role
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

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    /**
     * Aktifkan user yang statusnya masih 'pending'
     * Biasanya dipanggil oleh Admin setelah verifikasi manual
     */
    public function activateUser($id, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET status='active', role=? WHERE id=?");
        return $stmt->execute([$role, $id]);
    }

    public function getPendingUsers() {
        return $this->pdo->query("SELECT * FROM users WHERE status='pending'")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Simpan/Update Token Google OAuth untuk akses API Google Calendar
     * Refresh Token hanya disimpan jika ada (biasanya saat pertama kali connect)
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
