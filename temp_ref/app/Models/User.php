<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($nama, $email, $password, $role = 'student') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO {$this->table} (nama, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nama, $email, $hashed, $role]);
    }

    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>
