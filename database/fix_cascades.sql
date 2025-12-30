-- Skrip untuk mengganti nama Foreign Key agar seragam (Format: fk_tabel_relasi)
-- dan mengaktifkan ON DELETE CASCADE untuk semua relasi User.

-- ==========================================
-- 1. TABEL MAHASISWA (Relasi ke Users)
-- ==========================================
-- Hapus constraint lama (bawaan)
ALTER TABLE `mahasiswa` DROP FOREIGN KEY `mahasiswa_ibfk_1`;
-- Buat baru dengan format rapi & Cascade
ALTER TABLE `mahasiswa`
ADD CONSTRAINT `fk_mahasiswa_user`
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- ==========================================
-- 2. TABEL DOSEN (Relasi ke Users)
-- ==========================================
ALTER TABLE `dosen` DROP FOREIGN KEY `dosen_ibfk_1`;

ALTER TABLE `dosen`
ADD CONSTRAINT `fk_dosen_user`
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- ==========================================
-- 3. TABEL TASKS (Relasi ke Dosen/Users)
-- ==========================================
-- Relasi Dosen
ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_ibfk_1`;

ALTER TABLE `tasks`
ADD CONSTRAINT `fk_tasks_dosen`
FOREIGN KEY (`dosen_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Relasi Course (Sekalian dirapikan, opsional tapi bagus untuk konsistensi)
ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_ibfk_2`;

ALTER TABLE `tasks`
ADD CONSTRAINT `fk_tasks_course`
FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;


-- ==========================================
-- 4. TABEL ENROLLMENTS (Relasi ke Student)
-- ==========================================
ALTER TABLE `enrollments` DROP FOREIGN KEY `enrollments_ibfk_1`;

ALTER TABLE `enrollments`
ADD CONSTRAINT `fk_enrollments_student`
FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- ==========================================
-- 5. TABEL DOSEN COURSES (Relasi ke Dosen)
-- ==========================================
ALTER TABLE `dosen_courses` DROP FOREIGN KEY `dosen_courses_ibfk_1`;

ALTER TABLE `dosen_courses`
ADD CONSTRAINT `fk_dosen_courses_dosen`
FOREIGN KEY (`dosen_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- ==========================================
-- 6. TABEL TASK COMPLETIONS (Relasi ke User)
-- ==========================================
ALTER TABLE `task_completions` DROP FOREIGN KEY `task_completions_ibfk_1`;

ALTER TABLE `task_completions`
ADD CONSTRAINT `fk_task_completions_user`
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- ==========================================
-- 7. TABEL NOTIFICATIONS (Relasi ke User)
-- ==========================================
ALTER TABLE `notifications` DROP FOREIGN KEY `notifications_ibfk_1`;

ALTER TABLE `notifications`
ADD CONSTRAINT `fk_notifications_user`
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- ==========================================
-- 8. TABEL CLASS STUDENTS (Relasi ke Student - Jika belum ada)
-- ==========================================
-- Tabel ini sebelumnya tidak punya FK di dump, jadi kita Add saja.
-- Jika error "Duplicate", berarti sudah ada, abaikan.
ALTER TABLE `class_students`
ADD CONSTRAINT `fk_class_students_student`
FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
