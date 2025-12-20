<?php
class CourseModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Semua matkul + dosennya
    public function getAll()
    {
        $stmt = $this->pdo->query("
            SELECT 
                c.id,
                c.name,
                c.semester,
                GROUP_CONCAT(u.nama SEPARATOR ', ') AS dosen_pengajar
            FROM courses c
            LEFT JOIN dosen_courses dc ON dc.matkul_id = c.id
            LEFT JOIN users u ON u.id = dc.dosen_id
            GROUP BY c.id
            ORDER BY c.semester, c.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📌 Tambahan WAJIB: Dosen -> Mata Kuliah yang dia ampu
    public function getByDosen($dosen_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT c.*
            FROM courses c
            JOIN dosen_courses dc ON dc.matkul_id = c.id
            WHERE dc.dosen_id = ?
        ");
        $stmt->execute([$dosen_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $semester)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO courses (name, semester) VALUES (?, ?)"
        );
        return $stmt->execute([$name, $semester]);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $semester)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE courses SET name=?, semester=? WHERE id=?"
        );
        return $stmt->execute([$name, $semester, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM courses WHERE id=?");
        return $stmt->execute([$id]);
    }
}
