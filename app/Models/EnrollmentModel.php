<?php
// app/models/EnrollmentModel.php
class EnrollmentModel {
    private $db;
    public function __construct(PDO $pdo) { $this->db = $pdo; }

    // Enroll student to class
    public function enroll($student_id, $class_id) {
        // Check if already enrolled
        $stmt = $this->db->prepare("SELECT id FROM class_students WHERE student_id = ? AND class_id = ?");
        $stmt->execute([$student_id, $class_id]);
        if ($stmt->fetch()) return false;

        $stmt = $this->db->prepare("INSERT INTO class_students (student_id, class_id) VALUES (?, ?)");
        return $stmt->execute([$student_id, $class_id]);
    }

    // Get all enrollments with details
    public function getAll() {
        $stmt = $this->db->query("
            SELECT cs.id, u.nama as student_name, c.nama_kelas
            FROM class_students cs
            JOIN users u ON u.id = cs.student_id
            JOIN class c ON c.id_kelas = cs.class_id
            ORDER BY c.nama_kelas, u.nama
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil daftar Mahasiswa yang mengambil mata kuliah tertentu
     * Berguna saat Dosen ingin mengirim tugas ke kelas/matkul spesifik
     */
    public function getStudentsByCourse($course_id) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.email, u.nama as name
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            WHERE e.course_id = ?
        ");
        $stmt->execute([$course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Unenroll
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM class_students WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- New Features for Course Management ---

    /**
     * Cek apakah mahasiswa sudah terdaftar di mata kuliah tertentu
     */
    public function isEnrolled($student_id, $course_id) {
        $stmt = $this->db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$student_id, $course_id]);
        return $stmt->fetch() ? true : false;
    }

    public function enrollStudentToCourse($student_id, $course_id) {
        if ($this->isEnrolled($student_id, $course_id)) return false;
        
        $stmt = $this->db->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        return $stmt->execute([$student_id, $course_id]);
    }

    public function unenrollStudentFromCourse($student_id, $course_id) {
        $stmt = $this->db->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
        return $stmt->execute([$student_id, $course_id]);
    }

    /**
     * Daftarkan seluruh mahasiswa dari 'Kelas' (Tabel class_students) ke 'Mata Kuliah'
     * Ini mempercepat proses enrollment secara massal.
     */
    public function enrollClassToCourse($class_id, $course_id) {
        // 1. Ambil semua ID mahasiswa di kelas tersebut
        $stmt = $this->db->prepare("SELECT student_id FROM class_students WHERE class_id = ?");
        $stmt->execute([$class_id]);
        $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        // 2. Loop dan daftarkan satu per satu ke mata kuliah
        foreach ($students as $student_id) {
            // enrollStudentToCourse() sudah handle cek duplikat di dalamnya
            if ($this->enrollStudentToCourse($student_id, $course_id)) {
                $count++;
            }
        }
        return $count; // Kembalikan jumlah mahasiswa yang berhasil didaftarkan
    }
}
