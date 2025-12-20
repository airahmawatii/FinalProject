# 📝 Catatan Perubahan Struktur Database & Sistem

File ini mencatat perubahan penting yang dilakukan pada sistem dan database untuk mendukung fitur **Google Login**, **Google Calendar Sync**, dan **Refresh Token**.

## 1. Perubahan Database (`users` table)

Kolom berikut telah ditambahkan ke tabel `users` untuk menyimpan token OAuth Google:

```sql
ALTER TABLE `users` 
ADD COLUMN `access_token` text DEFAULT NULL,
ADD COLUMN `refresh_token` text DEFAULT NULL,
ADD COLUMN `token_expires` bigint(20) DEFAULT NULL;
```

*Catatan: File `setup_database/database_backup.sql` sudah mencakup perubahan ini.*

## 2. Fitur Baru System

### A. Auto-Refresh Token
- **File:** `app/Services/GoogleCalendarService.php`
- **Fungsi:** Sistem otomatis mendeteksi jika token akses Google kadaluarsa (biasanya 1 jam). Jika ada `refresh_token`, sistem akan meminta akses baru ke Google tanpa user perlu login ulang.

### B. Interactive Sidebar
- **File:** `public/views/layouts/sidebar_*.php`
- **Fungsi:** Sidebar sekarang bersifat responsif dan "menggeser" konten utama (push content) saat dibuka/tutup, bukan menutupi konten.
- **Berlaku untuk:** Admin, Dosen, dan Mahasiswa.

### C. Gantt Chart & Calendar Timeline
- **File:** `public/views/mahasiswa/dashboard_mahasiswa.php`
- **Fungsi:**
  1. Kalender menampilkan tugas sebagai **Range Bar** (dari tanggal buat s.d. deadline) bukan titik.
  2. Grafik timeline di bawah kalender menggunakan **ApexCharts RangeBar** yang sudah disesuaikan style-nya.

## 3. Konfigurasi Penting (.env)

Pastikan `Google Redirect URI` mengarah ke file callback yang benar:

```ini
GOOGLE_REDIRECT_URI=http://localhost/FinalProject/public/google_callback.php
```
*(Jangan arahkan ke index.php)*

---
**Diperbarui pada:** Desember 2025
**Oleh:** Tim Pengembang TaskAcademia
