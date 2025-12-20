# 📋 Final Testing Checklist - TaskAcademy

Sebelum presentasi/deploy, pastikan semua fitur berjalan dengan baik!

## ✅ **1. Authentication & Login**

### Login Manual
- [ ] Buka `http://localhost/FinalProject/public/index.php`
- [ ] Login sebagai Admin (`admin@gmail.com` / `admin123`)
- [ ] Logout
- [ ] Login sebagai Dosen (via Google atau manual jika sudah ada akun)
- [ ] Logout
- [ ] Login sebagai Mahasiswa (via Google atau manual)

### Google Login
- [ ] Klik "Masuk dengan Google"
- [ ] Pilih akun Google
- [ ] Redirect ke dashboard sesuai role
- [ ] Cek apakah data user tersimpan di database

---

## ✅ **2. Admin Features**

### User Management
- [ ] Approve user baru (Dosen/Mahasiswa)
- [ ] Lihat daftar semua user
- [ ] Edit data user
- [ ] Hapus user (test dengan user dummy)

### Bulk Import
- [ ] Import mahasiswa via CSV (`setup_database/contoh_mahasiswa.csv`)
- [ ] Cek apakah data masuk ke database
- [ ] Cek apakah mahasiswa bisa login

### Master Data
- [ ] Tambah Program Studi (Prodi)
- [ ] Tambah Angkatan
- [ ] Tambah Mata Kuliah
- [ ] Assign Dosen ke Mata Kuliah

---

## ✅ **3. Dosen Features**

### Manajemen Tugas
- [ ] Buat tugas baru dengan deadline
- [ ] Upload lampiran file (PDF/DOC)
- [ ] **Test Email:** Isi "Email Percobaan" dengan email teman
- [ ] Cek apakah email masuk ke inbox teman
- [ ] Edit tugas yang sudah dibuat
- [ ] Hapus tugas

### Monitoring
- [ ] Lihat daftar tugas
- [ ] Klik "Lihat Progres" pada tugas
- [ ] Cek apakah progress mahasiswa muncul (✅/❌)
- [ ] Lihat grafik beban kerja di dashboard

### Analytics
- [ ] Lihat grafik di dashboard
- [ ] Export data ke CSV
- [ ] Download & buka file CSV

---

## ✅ **4. Mahasiswa Features**

### Dashboard
- [ ] Lihat "Deadline Terdekat"
- [ ] Cek apakah tugas muncul di kalender
- [ ] Cek apakah Gantt Chart muncul

### Task Management
- [ ] Klik "Selesai" pada tugas
- [ ] Cek apakah status berubah (hijau)
- [ ] Klik "Batalkan" untuk undo
- [ ] Download lampiran tugas (jika ada)

### Share to Friend
- [ ] Klik tombol "To Teman 📤"
- [ ] Masukkan email teman
- [ ] Cek apakah email terkirim

---

## ✅ **5. Google Calendar Integration**

### Setup (Jika belum)
- [ ] Login via Google
- [ ] Buka Google Calendar di HP/Browser
- [ ] Cek apakah deadline muncul di kalender

### Testing
- [ ] Dosen buat tugas baru
- [ ] Mahasiswa login (yang pakai Google)
- [ ] Cek Google Calendar mahasiswa
- [ ] Deadline harus muncul otomatis

---

## ✅ **6. Email Notifications**

### Test Manual
- [ ] Dosen buat tugas dengan "Email Percobaan"
- [ ] Cek inbox email teman
- [ ] Email harus berisi:
  - ✅ Judul tugas
  - ✅ Deadline (tanggal & jam)
  - ✅ Nama dosen pengirim
  - ✅ Tombol "Buka Dashboard"

### Test Otomatis (H-1 Reminder)
- [ ] Buat tugas dengan deadline besok
- [ ] Jalankan `send_reminders.php` manual:
  ```bash
  php C:\xampp\htdocs\FinalProject\send_reminders.php
  ```
- [ ] Cek inbox mahasiswa
- [ ] Email reminder harus masuk

---

## ✅ **7. Responsive Design**

### Mobile Testing
- [ ] Buka di HP atau resize browser jadi kecil
- [ ] Klik hamburger menu (☰)
- [ ] Sidebar muncul dari kiri
- [ ] Semua fitur tetap bisa diakses
- [ ] Form tetap rapi (tidak overflow)

### Desktop Testing
- [ ] Sidebar tetap di kiri
- [ ] Layout rapi di layar besar
- [ ] Grafik & chart muncul dengan baik

---

## ✅ **8. Security & Error Handling**

### Access Control
- [ ] Logout dari Admin
- [ ] Coba akses URL Dosen langsung
- [ ] Harus redirect ke login
- [ ] Login sebagai Mahasiswa
- [ ] Coba akses URL Admin langsung
- [ ] Harus redirect/error

### Error Messages
- [ ] Login dengan password salah → Error muncul (SweetAlert)
- [ ] Upload file terlalu besar → Error muncul
- [ ] Form kosong → Validasi error

---

## ✅ **9. Database Export**

- [ ] Buka `http://localhost/phpmyadmin`
- [ ] Pilih database `fp`
- [ ] Tab "Export" → Klik "Go"
- [ ] Save file jadi `database_backup.sql`
- [ ] Pindahkan ke `setup_database/database_backup.sql`
- [ ] **PENTING:** Cek ukuran file (harus > 10 KB)

---

## ✅ **10. Final Cleanup**

### File yang Harus Dihapus
- [ ] `cleanup_database.php` (sudah dihapus otomatis)
- [ ] `cleanup_final.php` (sudah dihapus otomatis)
- [ ] `cek_user_admin.php` (file testing, hapus jika ada)

### File yang Harus Ada
- [ ] `README.md` (dengan nama kelompok)
- [ ] `.env.example` (template tanpa API key asli)
- [ ] `setup_database/database_backup.sql`
- [ ] `setup_database/buat_admin.php`
- [ ] `setup_database/contoh_mahasiswa.csv`

---

## 🎯 **Checklist Presentasi**

### Persiapan Demo
- [ ] XAMPP MySQL & Apache running
- [ ] Database sudah ada data dummy (minimal 3 mahasiswa, 2 dosen, 5 tugas)
- [ ] Browser sudah login sebagai Admin
- [ ] Tab lain siap untuk demo Dosen & Mahasiswa
- [ ] Email teman siap untuk demo notifikasi

### Skenario Demo (5-10 menit)
1. **Login** (30 detik)
   - Tunjukkan login page yang responsive
   - Login sebagai Admin

2. **Admin Panel** (2 menit)
   - Approve user baru
   - Import CSV mahasiswa
   - Assign dosen ke mata kuliah

3. **Dosen Dashboard** (3 menit)
   - Buat tugas baru
   - Upload lampiran
   - Test email ke teman (live!)
   - Lihat progress mahasiswa

4. **Mahasiswa Dashboard** (2 menit)
   - Lihat deadline terdekat
   - Tandai tugas selesai
   - Tunjukkan Google Calendar sync

5. **Penutup** (1 menit)
   - Tunjukkan grafik analytics
   - Export CSV
   - Selesai!

---

## 📝 **Catatan Penting**

### Kalau Ada Error Saat Demo:
- ✅ **Tenang!** Refresh halaman
- ✅ Cek XAMPP MySQL masih running
- ✅ Cek koneksi internet (untuk Google Login & Calendar)

### Pertanyaan Dosen yang Sering Muncul:
1. **"Ini pakai framework apa?"**
   → "Tidak pakai framework Pak/Bu, ini PHP Native dengan arsitektur MVC yang kami buat sendiri. Composer hanya untuk library seperti Google API dan PHPMailer."

2. **"Kenapa user cuma 1 tabel?"**
   → "Kami menggunakan prinsip Single Table Inheritance sesuai normalisasi 3NF, Pak/Bu. Semua user punya atribut dasar yang sama, jadi lebih efisien pakai kolom 'role' sebagai pembeda."

3. **"Email notifikasi pakai apa?"**
   → "Kami pakai PHPMailer dengan SMTP Gmail, Pak/Bu. Ada fitur reminder otomatis H-1 deadline via Cron Job."

4. **"Database-nya apa aja?"**
   → "Ada 12 tabel utama: users, courses, enrollments, tasks, task_completions, notifications, prodi, angkatan, dosen_courses, aktivitas, dan 2 tabel lainnya."

---

**✅ SISTEM SIAP PRESENTASI!** 🎉

**Good luck untuk presentasinya!** 🚀
