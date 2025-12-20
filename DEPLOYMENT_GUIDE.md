# 🚀 Panduan Deployment ke CyberPanel

**Tanggal:** 20 Desember 2025  
**Project:** TaskAcademia FinalProject

---

## 📋 Perubahan yang Dilakukan

### ✅ File yang Diperbaiki (akan auto-update via git pull):
1. `public/views/auth/login_view.php` - Fixed forgot password link
2. `public/views/auth/forgot_password.php` - Fixed reset password link & back to login
3. `public/views/auth/reset_password.php` - Fixed all navigation links
4. `public/reset_password.php` - Fixed login link
5. `public/views/admin/analytics.php` - Fixed SQL query (class_id → course_id)

### ❌ File yang Dihapus (perlu manual delete di server):
1. `public/forgot_password.php` - **HAPUS FILE INI DI CYBERPANEL**

---

## 🔄 Langkah-Langkah Deployment

### **Step 1: Commit & Push dari Localhost**

```bash
# Di localhost (Laragon)
cd C:\laragon\www\FinalProject

# Add semua perubahan
git add .

# Commit dengan pesan yang jelas
git commit -m "Fix: Analytics SQL query & forgot password routing"

# Push ke repository
git push origin main
```

---

### **Step 2: Pull di CyberPanel**

**Via SSH:**
```bash
# Login ke server via SSH
ssh user@your-server.com

# Masuk ke directory project
cd /home/yourdomain/public_html

# Pull perubahan terbaru
git pull origin main
```

**Via File Manager CyberPanel:**
1. Login ke CyberPanel
2. Buka **File Manager**
3. Masuk ke directory project
4. Klik **Git Pull** (jika tersedia)
5. Atau gunakan **Terminal** di CyberPanel

---

### **Step 3: Setup Database (PENTING!)**

⚠️ **Database perlu tabel tambahan yang belum ada di dump SQL Anda!**

**Via phpMyAdmin:**
1. Login ke phpMyAdmin di CyberPanel
2. Pilih database `nala_fp`
3. Klik tab "SQL"
4. Copy-paste query berikut:

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

5. Klik "Go"

**Via SSH:**
```bash
cd /home/yourdomain/public_html
mysql -u username -p nala_fp < database/create_submissions_table.sql
```

**Verifikasi:**
```sql
SHOW TABLES LIKE 'submissions';
DESCRIBE submissions;
```

---

### **Step 4: Manual Delete File yang Tidak Digunakan**

⚠️ **PENTING:** Git pull **TIDAK akan menghapus** file yang sudah dihapus dari repository!

**File yang harus dihapus manual di CyberPanel:**

```
public/forgot_password.php  ← HAPUS FILE INI
```

**Cara Hapus via File Manager:**
1. Buka File Manager di CyberPanel
2. Navigate ke: `/home/yourdomain/public_html/public/`
3. Cari file `forgot_password.php`
4. Klik kanan → Delete
5. Confirm deletion

**Cara Hapus via SSH:**
```bash
cd /home/yourdomain/public_html/public
rm forgot_password.php
```

---

### **Step 5: Verifikasi Perubahan**

Cek apakah semua file sudah benar:

```bash
# Cek struktur file
ls -la public/
ls -la public/views/auth/

# Pastikan forgot_password.php TIDAK ada di public/
# Pastikan forgot_password.php ADA di public/views/auth/
```

---

## 🎯 Checklist Deployment

- [ ] Commit & push dari localhost
- [ ] Git pull di CyberPanel
- [ ] **Hapus manual:** `public/forgot_password.php`
- [ ] Test forgot password flow
- [ ] Test reset password flow
- [ ] Test analytics page
- [ ] Verify semua link navigation

---

## 🔍 Testing Setelah Deployment

### 1. **Test Forgot Password:**
- Buka: `https://yourdomain.com/index.php?page=login`
- Klik "Forgot Access?"
- Harus redirect ke: `https://yourdomain.com/views/auth/forgot_password.php`
- Submit email
- Cek email untuk reset link

### 2. **Test Reset Password:**
- Klik link dari email
- Harus buka: `https://yourdomain.com/reset_password.php?token=...`
- Reset password
- Klik "Login Sekarang"
- Harus redirect ke login page

### 3. **Test Analytics:**
- Login sebagai admin
- Buka Analytics page
- **Tidak boleh ada error** "Unknown column 't.class_id'"
- Chart harus muncul dengan benar

---

## ⚠️ Troubleshooting

### **Problem: File lama masih ada di server**
**Solusi:** Hapus manual via File Manager atau SSH

### **Problem: Git pull error "local changes"**
**Solusi:**
```bash
# Backup perubahan lokal di server (jika ada)
git stash

# Pull perubahan
git pull origin main

# Restore perubahan lokal (jika perlu)
git stash pop
```

### **Problem: Permission denied**
**Solusi:**
```bash
# Set permission yang benar
chmod -R 755 /home/yourdomain/public_html
chown -R username:username /home/yourdomain/public_html
```

---

## 📊 Summary Perubahan Database

**Tabel yang Digunakan:**
- ✅ `tasks` (bukan `tugas`)
- ✅ `tasks.course_id` (bukan `class_id`)
- ✅ `class.id_kelas`
- ✅ `password_resets`
- ✅ `users`
- ✅ `notifications`
- ✅ `submissions`

**Tidak ada perubahan database schema** - hanya perbaikan query SQL.

---

## 🎉 Hasil Akhir

Setelah deployment berhasil:
- ✅ Forgot password flow berfungsi dengan benar
- ✅ Reset password flow berfungsi dengan benar
- ✅ Analytics page tidak error
- ✅ Semua link navigation konsisten menggunakan BASE_URL
- ✅ Tidak ada duplikasi file forgot_password.php

---

**Generated by:** Antigravity AI  
**Date:** 2025-12-20  
**Version:** 1.0
