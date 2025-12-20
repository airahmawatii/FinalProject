-- Tabel submissions untuk tracking pengumpulan tugas
-- Jalankan query ini di database CyberPanel jika tabel belum ada

CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('submitted','late','graded') DEFAULT 'submitted',
  `grade` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_submissions_task` (`task_id`),
  KEY `fk_submissions_student` (`student_id`),
  CONSTRAINT `fk_submissions_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_submissions_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
