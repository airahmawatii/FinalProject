<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nama, $email, $password, $role) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (nama, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$nama, $email, $password, $role]);
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
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $nama, $email, $role) {
        $stmt = $this->pdo->prepare("
            UPDATE users SET nama=?, email=?, role=? WHERE id=?
        ");
        return $stmt->execute([$nama, $email, $role, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function activateUser($id, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET status='active', role=? WHERE id=?");
        return $stmt->execute([$role, $id]);
    }

    public function getPendingUsers() {
        return $this->pdo->query("SELECT * FROM users WHERE status='pending'")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateGoogleTokens($id, $accessToken, $refreshToken, $expires) {
        if ($refreshToken) {
            $stmt = $this->pdo->prepare("UPDATE users SET access_token=?, refresh_token=?, token_expires=? WHERE id=?");
            return $stmt->execute([$accessToken, $refreshToken, $expires, $id]);
        } else {
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
