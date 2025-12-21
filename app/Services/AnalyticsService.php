<?php

require_once __DIR__ . '/../config/database.php';

class AnalyticsService
{
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->connect();
    }

    /**
     * Get Total Stats for Dashboard
     */
    public function getDashboardStats()
    {
        return [
            'total_users' => $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'total_dosen' => $this->pdo->query("SELECT COUNT(*) FROM users WHERE role='dosen'")->fetchColumn(),
            'total_mahasiswa' => $this->pdo->query("SELECT COUNT(*) FROM users WHERE role='mahasiswa'")->fetchColumn(),
            'total_tasks' => $this->pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn(),
            'active_students' => $this->pdo->query("SELECT COUNT(*) FROM users WHERE role='mahasiswa' AND status='active'")->fetchColumn(),
        ];
    }

    /**
     * Calculate Workload (Tasks per Course)
     */
    /**
     * Calculate Workload (Tasks per Course)
     */
    public function getWorkloadStats($dosen_id = null)
    {
        $sql = "
            SELECT c.name as course_name, COUNT(t.id) as task_count
            FROM courses c
            LEFT JOIN tasks t ON c.id = t.course_id
        ";

        if ($dosen_id) {
            // Filter by Dosen via dosen_courses table or tasks.dosen_id
            // Using tasks.dosen_id is safer as it reflects actual tasks created by this dosen
            $sql .= " WHERE t.dosen_id = :dosen_id ";
        }

        $sql .= "
            GROUP BY c.id, c.name
            ORDER BY task_count DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        if ($dosen_id) {
            $stmt->execute(['dosen_id' => $dosen_id]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate Workload per Month (for Line Chart)
     */
    public function getTasksPerMonth($dosen_id = null)
    {
        $sql = "
            SELECT DATE_FORMAT(deadline, '%Y-%m') as month, COUNT(*) as count
            FROM tasks
        ";

        if ($dosen_id) {
            $sql .= " WHERE dosen_id = :dosen_id ";
        }

        $sql .= "
            GROUP BY month
            ORDER BY month ASC
            LIMIT 12
        ";
        
        $stmt = $this->pdo->prepare($sql);
        if ($dosen_id) {
            $stmt->execute(['dosen_id' => $dosen_id]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate CSV content for Tasks Report
     */
    public function generateTasksCSV($dosen_id = null)
    {
        $sql = "
            SELECT t.task_title, c.name as course_name, u.nama as dosen_name, t.deadline, t.created_at
            FROM tasks t
            JOIN courses c ON t.course_id = c.id
            LEFT JOIN users u ON t.dosen_id = u.id
        ";

        if ($dosen_id) {
            $sql .= " WHERE t.dosen_id = :dosen_id ";
        }

        $sql .= " ORDER BY t.deadline DESC ";

        $stmt = $this->pdo->prepare($sql);
        if ($dosen_id) {
            $stmt->execute(['dosen_id' => $dosen_id]);
        } else {
            $stmt->execute();
        }
        
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Open memory stream
        $f = fopen('php://memory', 'w');
        
        // Headers
        fputcsv($f, ['Judul Tugas', 'Mata Kuliah', 'Dosen', 'Deadline', 'Dibuat Pada']);

        // Data
        foreach ($tasks as $task) {
            fputcsv($f, $task);
        }

        // Reset pointer
        fseek($f, 0);
        
        return stream_get_contents($f);
    }
    /**
     * Get Tasks Data for Reporting (PDF/HTML)
     */
    public function getTasksReportData($dosen_id = null)
    {
        $sql = "
            SELECT t.task_title, c.name as course_name, u.nama as dosen_name, t.deadline, t.created_at
            FROM tasks t
            JOIN courses c ON t.course_id = c.id
            LEFT JOIN users u ON t.dosen_id = u.id
        ";

        if ($dosen_id) {
            $sql .= " WHERE t.dosen_id = :dosen_id ";
        }

        $sql .= " ORDER BY t.deadline DESC ";

        $stmt = $this->pdo->prepare($sql);
        if ($dosen_id) {
            $stmt->execute(['dosen_id' => $dosen_id]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
