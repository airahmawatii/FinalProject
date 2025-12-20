# 📚 TaskAcademy - Sistem Manajemen Deadline Akademik

> **Sistem Web Deadline Akademik** berbasis PHP Native dengan fitur Google Calendar Sync, Email Reminder, dan Progress Monitoring untuk Dosen & Mahasiswa.

---

## 🎯 Fitur Utama

### 👨‍🏫 **Untuk Dosen:**
- ✅ Buat & Kelola Tugas (CRUD)
- ✅ Upload Lampiran File (PDF, DOC, dll)
- ✅ Monitoring Progress Mahasiswa (Siapa yang sudah selesai)
- ✅ Analitik Beban Kerja (Grafik & Export CSV)
- ✅ Email Notifikasi Otomatis ke Mahasiswa

### 👨‍🎓 **Untuk Mahasiswa:**
- ✅ Dashboard Deadline Terdekat
- ✅ **Google Calendar Auto-Sync** (Tugas otomatis masuk kalender user)
- ✅ **Gantt Chart Timeline** (Visualisasi durasi pengerjaan tugas)
- ✅ Email Reminder H-1 Deadline
- ✅ Tandai Tugas Selesai
- ✅ Download Lampiran Tugas

### 👨‍💼 **Untuk Admin:**
- ✅ Approve User Baru (Dosen/Mahasiswa)
- ✅ Bulk Import Mahasiswa (CSV)
- ✅ Kelola Mata Kuliah, Kelas, & Prodi
- ✅ Penugasan Dosen (Enrollment)

---

## 🛠️ Tech Stack

- **Backend:** PHP 7.4 - 8.2 (Native, No Framework)
- **Database:** MySQL / MariaDB
- **Frontend:** Tailwind CSS (CDN), JavaScript Vanilla, SweetAlert2
- **Visuals:** ApexCharts (Gantt/Timeline), Chart.js (Analytics), FullCalendar
- **Libraries:** 
  - Google API Client (OAuth 2.0 & Calendar)
  - PHPMailer (Email Notifications)
  - DomPDF (Export PDF)
  - Dotenv (Environment Variables)

---

## 📦 Instalasi

### 1. **Clone Repository**
```bash
git clone https://github.com/username/TaskAcademy.git
cd TaskAcademy
```

### 2. **Install Dependencies**
```bash
composer install
```

### 3. **Setup Database**
1. Buat database baru di MySQL (misal: `fp`):
   ```sql
   CREATE DATABASE fp;
   ```

2. Import struktur & data awal:
   ```bash
   mysql -u root -p fp < setup_database/database_backup.sql
   ```
   *(File ini sudah berisi tabel terbaru termasuk kolom token Google)*

### 4. **Konfigurasi Environment**
1. Copy file `.env.example` menjadi `.env`:
   ```bash
   copy .env.example .env
   ```

2. Edit file `.env` dan isi kredensial:
   ```ini
   # Database
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fp
   DB_USERNAME=root
   DB_PASSWORD=

   # Google OAuth (Dapatkan dari Google Cloud Console)
   GOOGLE_CLIENT_ID=your_client_id_here
   GOOGLE_CLIENT_SECRET=your_client_secret_here
   # PENTING: URI harus mengarah ke google_callback.php
   GOOGLE_REDIRECT_URI=http://localhost/FinalProject/public/google_callback.php

   # Email SMTP (Gmail App Password)
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=your_email@gmail.com
   SMTP_PASS=your_app_password
   ```

### 5. **Setup Admin User**
Jika belum ada di database backup, jalankan script ini:
```bash
php setup_database/buat_admin.php
```

**Login Admin Default:**
- Email: `admin@gmail.com`
- Password: `admin123`

### 6. **Jalankan Aplikasi**
Pastikan server (Apache/Nginx) mengarah ke folder project. Buka di browser:
```
http://localhost/FinalProject/public/index.php
```

---

## ⚙️ Konfigurasi Tambahan

### **Google Calendar Integration**
1. Buka [Google Cloud Console](https://console.cloud.google.com/).
2. Buat Project & Enable **Google Calendar API**.
3. Buat Credentials **OAuth Client ID** (Web Application).
4. **Authorized Redirect URIs**: Masukkan `http://localhost/FinalProject/public/google_callback.php` (sesuaikan domain).
5. Copy Client ID & Secret ke `.env`.
6. Saat login pertama kali, user akan diminta izin akses kalender.

### **Email Reminder (Cron Job)**
Untuk mengirim email reminder H-1 deadline secara otomatis:

**Windows (Task Scheduler):**
Command: `C:\path\to\php.exe`
Arguments: `C:\path\to\FinalProject\skrip_otomatis\kirim_pengingat.php`
Trigger: Daily at 08:00 AM.

---

## 📂 Struktur Folder
```
FinalProject/
├── app/
│   ├── config/          # Koneksi Database
│   ├── Controllers/     # Logic Auth & User
│   ├── Models/          # Query Database
│   └── Services/        # Google Service & Email Logic
├── public/
│   ├── api/             # Endpoints (JSON) untuk Charts/Calendar
│   ├── views/           # Tampilan (Admin/Dosen/Mhs)
│   │   ├── layouts/     # Sidebar & Header
│   ├── uploads/         # File Tugas
│   ├── google_callback.php # Handler OAuth
│   └── index.php        # Entry Point
├── setup_database/      # SQL Backup & Script Admin
├── skrip_otomatis/      # Script Cron Job
└── .env                 # Konfigurasi Private
```

---

## 🐛 Troubleshooting

### **Google Token Expired / Error Sync**
- Sistem kini otomatis me-refresh token akses jika kadaluarsa.
- Jika tetap gagal, user cukup **Logout** lalu **Login kembali** via Google untuk memperbarui token.

### **Error "Redirect URI Mismatch"**
- Pastikan URL di `.env` SAMA PERSIS dengan yang didaftarkan di Google Cloud Console (termasuk http/https dan slash di akhir jika ada, tapi sebaiknya tanpa slash).
- Benar: `.../public/google_callback.php`

---

## 👨‍💻 Development Team

**Kelompok:**
- **Alya Novita Putri** - NIM: 24416255201159
- **Ai Rahmawati** - NIM: 24416255201160
- **Nayla Farras Nafizah** - NIM: 24416255201163

**Universitas Buana Perjuangan Karawang**  
Teknik Informatika - 2024

---

**⚡ TaskAcademy** - *Deadline Terpantau, Nilai Aman!*
