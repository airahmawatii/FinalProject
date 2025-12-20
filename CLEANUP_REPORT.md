# 🧹 Project Cleanup Report

## ❌ **FILE YANG HARUS DIHAPUS:**

### **1. Setup Files (Sudah Dijalankan)**
- [ ] `setup_password_reset.php` (root) - Setup tabel password_resets
- [ ] `public/setup_attachment_column.php` - Setup kolom attachment
- [ ] `public/setup_completion_table.php` - Setup tabel task_completions
- [ ] `public/setup_photo_column.php` - Setup kolom photo

**Alasan:** File setup ini cuma dijalanin sekali. Kalau sudah jalan, hapus aja biar gak disalahgunakan.

---

### **2. File Testing/Debug**
- [ ] `public/generate_hash.php` - Generate password hash (testing)
- [ ] `public/lupa_password.php` - File lama (sudah ada `views/auth/forgot_password.php`)

**Alasan:** File testing yang gak kepake lagi.

---

### **3. File Login Duplikat**
- [ ] `public/views/auth/login.php` - Duplikat (pakai `login_view.php`)
- [ ] `public/views/auth/login_manual.php` - Duplikat (pakai `login_view.php`)
- [ ] `public/views/auth/admin_login.php` - Duplikat (admin login via `login_view.php`)

**Alasan:** Kamu cuma butuh 1 file login: `login_view.php`

---

### **4. File Logout Lama**
- [ ] `public/views/dosen/logout.php` - Sudah ada `public/logout.php` (unified)

**Alasan:** Sudah pakai unified logout di root.

---

## ⚠️ **FILE YANG PERLU DIBENERIN:**

### **1. Sidebar Dosen - Link Logout**
**File:** `public/views/layouts/sidebar_dosen.php`
**Masalah:** Mungkin masih pakai link logout lama
**Fix:** Ganti jadi `/FinalProject/public/logout.php`

---

### **2. Admin Dashboard - Link Logout**
**File:** `public/views/admin/dashboard_admin.php`
**Masalah:** Mungkin masih pakai link logout lama
**Fix:** Ganti jadi `/FinalProject/public/logout.php`

---

### **3. Daftar Tugas - Success Message**
**File:** `public/views/dosen/daftar_tugas.php`
**Masalah:** Belum ada feedback setelah hapus tugas
**Fix:** Tambah SweetAlert untuk `?msg=deleted`

---

## 📊 **SUMMARY:**

| Kategori | Jumlah File | Action |
|----------|-------------|--------|
| Setup Files | 4 | **HAPUS** |
| Testing Files | 2 | **HAPUS** |
| Login Duplikat | 3 | **HAPUS** |
| Logout Lama | 1 | **HAPUS** |
| **TOTAL HAPUS** | **10 files** | |
| Files to Fix | 3 | **BENERIN** |

---

## ✅ **EXECUTION PLAN:**

### **Step 1: Hapus File (10 files)**
Saya akan hapus semua file yang gak guna.

### **Step 2: Fix Links (3 files)**
Saya akan benerin link logout & tambah success message.

### **Step 3: Verify**
Test semua fitur masih jalan.

---

**Estimasi Waktu:** 5 menit
**Risk:** Low (cuma hapus file yang gak kepake)

**Siap dijalankan sekarang?**
