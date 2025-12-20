# 🗄️ Database Schema Adjustment Guide

**Database Name:** `nala_fp` (CyberPanel)  
**Tanggal:** 20 Desember 2025

---

## 📋 Struktur Database yang Sudah Sesuai

### ✅ Tabel yang Sudah Ada & Benar:

1. **`users`** - Pengguna sistem (admin, dosen, mahasiswa)
2. **`tasks`** - Tugas yang dibuat dosen
   - Menggunakan `course_id` ✅ (bukan `class_id`)
3. **`courses`** - Mata kuliah
4. **`class`** - Kelas (IF-A, IF-B, dll)
5. **`angkatan`** - Tahun angkatan
6. **`prodi`** - Program studi
7. **`enrollments`** - Pendaftaran mahasiswa ke mata kuliah
8. **`class_students`** - Mahasiswa di kelas tertentu
9. **`dosen_courses`** - Mata kuliah yang diampu dosen
10. **`notifications`** - Log notifikasi email
11. **`password_resets`** - Token reset password
12. **`task_completions`** - Tracking tugas yang sudah diselesaikan
13. **`aktivitas`** - Log aktivitas sistem

---

## ⚠️ Tabel yang Perlu Ditambahkan

### 1. Tabel `submissions` (BELUM ADA)

**Fungsi:** Menyimpan data pengumpulan tugas mahasiswa

**SQL untuk membuat tabel:**

```sql
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
```

**Cara Menjalankan:**

**Via phpMyAdmin CyberPanel:**
1. Login ke phpMyAdmin
2. Pilih database `nala_fp`
3. Klik tab "SQL"
4. Copy-paste query di atas
5. Klik "Go"

**Via SSH:**
```bash
mysql -u username -p nala_fp < /path/to/create_submissions_table.sql
```

---

## 🔧 Penyesuaian Kode yang Sudah Dilakukan

### 1. ✅ Analytics.php - Fixed SQL Query
**File:** `public/views/admin/analytics.php`

**Perubahan:**
```php
// ❌ SEBELUM (SALAH)
LEFT JOIN tasks t ON c.id_kelas = t.class_id

// ✅ SESUDAH (BENAR)
LEFT JOIN tasks t ON c.id_kelas = t.course_id
```

**Alasan:** Tabel `tasks` menggunakan kolom `course_id`, bukan `class_id`

---

## 📊 Mapping Tabel & Kolom

### Tabel `tasks`
```
✅ id (int)
✅ dosen_id (int) → FK ke users.id
✅ course_id (int) → FK ke courses.id (BUKAN class_id!)
✅ task_title (varchar)
✅ description (text)
✅ attachment (varchar)
✅ deadline (datetime)
✅ created_at (datetime)
```

### Tabel `class`
```
✅ id_kelas (int) → Primary Key
✅ nama_kelas (varchar) → Nama kelas (IF-A, IF-B, dll)
✅ prodi_id (int) → FK ke prodi.id_prodi
✅ angkatan_id (int) → FK ke angkatan.id_angkatan
```

### Tabel `courses`
```
✅ id (int) → Primary Key
✅ name (varchar) → Nama mata kuliah
✅ semester (varchar) → Semester
✅ created_at (datetime)
```

---

## 🔗 Relasi Antar Tabel

```
users (dosen) ──┬─→ dosen_courses ──→ courses
                │
                └─→ tasks ──→ courses
                
users (mahasiswa) ──┬─→ enrollments ──→ courses
                    │
                    ├─→ class_students ──→ class
                    │
                    ├─→ submissions ──→ tasks
                    │
                    └─→ task_completions ──→ tasks

class ──→ prodi
class ──→ angkatan
```

---

## 📝 Checklist Deployment ke CyberPanel

### Sebelum Git Pull:

- [ ] Backup database terlebih dahulu
- [ ] Catat struktur tabel yang ada

### Setelah Git Pull:

- [ ] Jalankan SQL untuk membuat tabel `submissions`
- [ ] Verifikasi semua tabel ada
- [ ] Test analytics page (tidak boleh error)
- [ ] Test create task (harus bisa)
- [ ] Test submit task (jika ada fitur)

### SQL Commands untuk Verifikasi:

```sql
-- Cek semua tabel
SHOW TABLES;

-- Cek struktur tabel tasks
DESCRIBE tasks;

-- Cek apakah tabel submissions ada
SHOW TABLES LIKE 'submissions';

-- Cek data sample
SELECT COUNT(*) FROM tasks;
SELECT COUNT(*) FROM users WHERE role='mahasiswa';
SELECT COUNT(*) FROM class;
```

---

## 🚨 Catatan Penting

### 1. **Nama Kolom yang Berbeda:**
- ❌ `tasks.class_id` → TIDAK ADA
- ✅ `tasks.course_id` → YANG BENAR

### 2. **Primary Key yang Berbeda:**
- `class` menggunakan `id_kelas` (bukan `id`)
- `prodi` menggunakan `id_prodi` (bukan `id`)
- `angkatan` menggunakan `id_angkatan` (bukan `id`)

### 3. **Tabel Submissions:**
- Digunakan oleh `analytics.php` untuk grafik pengumpulan tugas
- **HARUS dibuat** di database CyberPanel
- Jika tidak ada, analytics page akan error

---

## 🎯 Kesimpulan

**Status Database:**
- ✅ Struktur utama sudah sesuai
- ⚠️ Perlu tambah tabel `submissions`
- ✅ Kode sudah disesuaikan dengan struktur database CyberPanel

**File SQL yang Disediakan:**
- `database/create_submissions_table.sql` - Untuk membuat tabel submissions

**Next Steps:**
1. Git pull di CyberPanel
2. Jalankan SQL untuk create tabel `submissions`
3. Test semua fitur
4. Verifikasi tidak ada error

---

**Generated by:** Antigravity AI  
**Date:** 2025-12-20  
**Database:** nala_fp (CyberPanel)
