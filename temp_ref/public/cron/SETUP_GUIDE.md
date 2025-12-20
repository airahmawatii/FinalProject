# H-1 Auto Notification System - Setup Guide

## Cara Setup Windows Task Scheduler

### Langkah 1: Buka Task Scheduler
1. Tekan `Win + R`
2. Ketik `taskschd.msc`
3. Tekan Enter

### Langkah 2: Buat Task Baru
1. Klik **"Create Basic Task"** di panel kanan
2. Name: `TaskAcademia H-1 Reminder`
3. Description: `Mengirim notifikasi H-1 untuk tugas yang deadline besok`
4. Klik **Next**

### Langkah 3: Set Trigger (Kapan Dijalankan)
1. Pilih **"Daily"**
2. Klik **Next**
3. Set waktu: **08:00 AM** (atau jam berapa pun kamu mau)
4. Recur every: **1 days**
5. Klik **Next**

### Langkah 4: Set Action (Apa yang Dijalankan)
1. Pilih **"Start a program"**
2. Klik **Next**
3. Program/script: Browse ke file batch
   ```
   C:\laragon\www\FinalProject\public\cron\run_h1_reminders.bat
   ```
4. Klik **Next**

### Langkah 5: Finish
1. Centang **"Open the Properties dialog..."**
2. Klik **Finish**

### Langkah 6: Konfigurasi Advanced (Optional tapi Recommended)
Di Properties dialog yang terbuka:

**Tab General:**
- Centang: **"Run whether user is logged on or not"**
- Centang: **"Run with highest privileges"**

**Tab Conditions:**
- Uncheck: **"Start the task only if the computer is on AC power"**
- Uncheck: **"Stop if the computer switches to battery power"**

**Tab Settings:**
- Centang: **"Run task as soon as possible after a scheduled start is missed"**
- Centang: **"If the task fails, restart every: 10 minutes"**
- Set: **"Attempt to restart up to: 3 times"**

Klik **OK** untuk save.

---

## Testing Manual

### Test 1: Jalankan Script Manual
Buka Command Prompt dan jalankan:
```cmd
cd C:\laragon\www\FinalProject\public\cron
run_h1_reminders.bat
```

Cek log file di:
```
C:\laragon\www\FinalProject\public\cron\logs\h1_reminders.log
```

### Test 2: Test dengan Data Dummy
1. Buat tugas baru dengan deadline besok
2. Tunggu atau jalankan script manual
3. Cek email mahasiswa yang terdaftar di kelas tersebut

### Test 3: Test Task Scheduler
1. Buka Task Scheduler
2. Cari task **"TaskAcademia H-1 Reminder"**
3. Klik kanan → **Run**
4. Cek log file untuk memastikan berhasil

---

## Troubleshooting

### Email Tidak Terkirim
- Cek konfigurasi SMTP di `.env`
- Pastikan `SMTP_HOST`, `SMTP_USER`, `SMTP_PASS` sudah benar
- Cek log file untuk error message

### Script Tidak Jalan
- Pastikan path PHP benar di batch file
- Cek apakah Laragon sedang running
- Cek permission folder `cron/logs`

### Task Scheduler Tidak Jalan
- Pastikan Windows Task Scheduler service running
- Cek event log Windows untuk error
- Pastikan user account punya permission

---

## Monitoring

### Cek Log File
Log file tersimpan di:
```
C:\laragon\www\FinalProject\public\cron\logs\h1_reminders.log
```

Log akan menampilkan:
- Jumlah tugas yang ditemukan
- Email yang terkirim
- Error jika ada

### Cek Database
Query untuk cek notifikasi yang terkirim:
```sql
SELECT * FROM notifications 
WHERE channel = 'email' 
AND message LIKE '%REMINDER H-1%'
ORDER BY created_at DESC;
```

---

## Cara Kerja Sistem

1. **Setiap hari jam 08:00 AM**, Task Scheduler menjalankan batch file
2. **Batch file** memanggil script PHP `send_h1_reminders.php`
3. **Script PHP** akan:
   - Cek database untuk tugas yang deadline besok
   - Ambil daftar mahasiswa yang terdaftar di kelas tersebut
   - Kirim email reminder ke setiap mahasiswa
   - Log hasil ke file log
4. **Email reminder** berisi:
   - Judul tugas
   - Nama mata kuliah
   - Deadline (tanggal dan jam WIB)
   - Deskripsi tugas
   - Link ke dashboard

---

## Customization

### Ubah Waktu Pengiriman
Edit trigger di Task Scheduler atau ubah waktu di step 3 setup.

### Ubah Template Email
Edit file `send_h1_reminders.php` bagian `$emailBody`.

### Ubah Kapan Reminder Dikirim
Saat ini: H-1 (1 hari sebelum deadline)
Untuk ubah ke H-2 atau H-3, edit di `send_h1_reminders.php`:
```php
// Untuk H-2 (2 hari sebelum)
$tomorrow = date('Y-m-d', strtotime('+2 day'));

// Untuk H-3 (3 hari sebelum)
$tomorrow = date('Y-m-d', strtotime('+3 day'));
```
