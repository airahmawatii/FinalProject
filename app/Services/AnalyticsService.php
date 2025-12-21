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

    /**
     * Get Recommendations and Insights for Dosen
     */
    public function getRecommendations($dosen_id)
    {
        $recommendations = [];

        // 1. Check for courses with NO tasks
        $stmt = $this->pdo->prepare("
            SELECT name FROM courses c
            JOIN dosen_courses dc ON c.id = dc.matkul_id
            WHERE dc.dosen_id = ? 
            AND c.id NOT IN (SELECT course_id FROM tasks WHERE dosen_id = ?)
        ");
        $stmt->execute([$dosen_id, $dosen_id]);
        $lazyCourses = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($lazyCourses as $course) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => "Mata kuliah '$course' belum memiliki tugas. Mahasiswa mungkin butuh latihan!"
            ];
        }

        // 2. Check for upcoming deadlines (next 48 hours)
        $stmt = $this->pdo->prepare("
            SELECT t.task_title, c.name as course_name 
            FROM tasks t
            JOIN courses c ON t.course_id = c.id
            WHERE t.dosen_id = ? 
            AND t.deadline > NOW() 
            AND t.deadline < DATE_ADD(NOW(), INTERVAL 48 HOUR)
        ");
        $stmt->execute([$dosen_id]);
        $upcomingTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($upcomingTasks as $task) {
            $recommendations[] = [
                'type' => 'info',
                'message' => "Tenggat waktu tugas '{$task['task_title']}' ({$task['course_name']}) akan segera berakhir dalam 48 jam."
            ];
        }

        // 3. Overall health check
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'message' => "Semua kelas terpantau aman dan terkelola dengan baik. Kerja bagus!"
            ];
        }

        return $recommendations;
    }
}
