<?php
// app/models/DosenCourseModel.php
class DosenCourseModel {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    // Ambil semua relasi dosen ke matkul
    public function getAll() {
        $stmt = $this->db->query("
            SELECT dc.id, u.nama AS dosen_name, c.name AS course_name
            FROM dosen_courses dc
            JOIN users u ON u.id = dc.dosen_id
            JOIN courses c ON c.id = dc.matkul_id
            ORDER BY u.nama
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Assign dosen ke matkul
    public function assign($dosen_id, $course_id) {
        $stmt = $this->db->prepare("
            INSERT INTO dosen_courses (dosen_id, matkul_id)
            VALUES (?, ?)
        ");
        return $stmt->execute([$dosen_id, $course_id]);
    }

    // Cek apakah sudah ada assign
    public function exists($dosen_id, $course_id) {
        $stmt = $this->db->prepare("
            SELECT id FROM dosen_courses WHERE dosen_id = ? AND matkul_id = ?
        ");
        $stmt->execute([$dosen_id, $course_id]);
        return $stmt->fetch() ? true : false;
    }
}
