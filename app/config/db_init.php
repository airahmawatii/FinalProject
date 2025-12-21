<?php
/**
 * Automated Database Fix - Ensures 'class' and 'class_students' tables exist.
 */
require_once __DIR__ . '/database.php';

try {
    $db = new Database();
    $pdo = $db->connect();

    if ($pdo) {
        // SQL for creating table 'class'
        $sql = "
        CREATE TABLE IF NOT EXISTS `class` (
          `id_kelas` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `nama_kelas` varchar(50) NOT NULL,
          `prodi_id` int(10) UNSIGNED NOT NULL,
          `angkatan_id` int(10) UNSIGNED NOT NULL,
          `created_at` datetime DEFAULT current_timestamp(),
          PRIMARY KEY (`id_kelas`),
          KEY `idx_prodi` (`prodi_id`),
          KEY `idx_angkatan` (`angkatan_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql);

        // SQL for class_students
        $sql_cs = "
        CREATE TABLE IF NOT EXISTS `class_students` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `student_id` int(11) NOT NULL,
          `class_id` int(10) UNSIGNED NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `student_id` (`student_id`),
          KEY `class_id` (`class_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $pdo->exec($sql_cs);
    }
} catch (Exception $e) {
    // Silent fail in production
}
?>