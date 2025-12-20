<?php
// app/models/TaskModel.php
class TaskModel
{
    private $db;
    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getByDosen($dosen_id)
    {
        $stmt = $this->db->prepare("SELECT tasks.*, courses.name AS course_name 
            FROM tasks JOIN courses ON courses.id = tasks.course_id
            WHERE tasks.dosen_id = ? ORDER BY tasks.created_at DESC");
        $stmt->execute([$dosen_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStudent($student_id)
    {
        // Asumsi tabel enrollments menghubungkan student_id dan course_id
        $stmt = $this->db->prepare("
            SELECT t.*, c.name AS course_name, u.nama AS dosen_name,
                   tc.completed_at IS NOT NULL as is_completed
            FROM tasks t
            JOIN enrollments e ON e.course_id = t.course_id
            JOIN courses c ON c.id = t.course_id
            JOIN users u ON u.id = t.dosen_id
            LEFT JOIN task_completions tc ON tc.task_id = t.id AND tc.user_id = ?
            WHERE e.student_id = ?
            ORDER BY t.deadline ASC
        ");
        $stmt->execute([$student_id, $student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleCompletion($task_id, $user_id)
    {
        // Check if already completed
        $stmt = $this->db->prepare("SELECT id FROM task_completions WHERE task_id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Unmark
            $stmt = $this->db->prepare("DELETE FROM task_completions WHERE task_id = ? AND user_id = ?");
            $stmt->execute([$task_id, $user_id]);
            return false; // Not completed
        } else {
            // Mark
            $stmt = $this->db->prepare("INSERT INTO task_completions (task_id, user_id) VALUES (?, ?)");
            $stmt->execute([$task_id, $user_id]);
            return true; // Completed
        }
    }

    public function getTaskProgress($task_id)
    {
        // 1. Get Course ID from Task
        $stmt = $this->db->prepare("SELECT course_id FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $task = $stmt->fetch();

        if (!$task) return [];

        // 2. Get All Students in that Course + Completion Status
        $stmt = $this->db->prepare("
            SELECT u.id, u.nama, u.email,
                   tc.completed_at
            FROM users u
            JOIN enrollments e ON e.student_id = u.id
            LEFT JOIN task_completions tc ON tc.user_id = u.id AND tc.task_id = ?
            WHERE e.course_id = ? AND u.role = 'mahasiswa'
            ORDER BY u.nama ASC
        ");
        $stmt->execute([$task_id, $task['course_id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($dosen_id, $course_id, $task_title, $description, $deadline, $attachment = null)
    {
        $stmt = $this->db->prepare("INSERT INTO tasks (dosen_id, course_id, task_title, description, deadline, attachment) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$dosen_id, $course_id, $task_title, $description, $deadline, $attachment]);
    }
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $judul, $deskripsi, $deadline, $course_id)
    {
        $stmt = $this->db->prepare("UPDATE tasks SET task_title=?, description=?, deadline=?, course_id=? WHERE id=?");
        return $stmt->execute([$judul, $deskripsi, $deadline, $course_id, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id=?");
        return $stmt->execute([$id]);
    }
}
