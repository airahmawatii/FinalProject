# Fitur Lupa Password - User Guide

## **Fitur yang Sudah Dibuat:**

1. **Forgot Password Page** - Form input email
2. **Email Notification** - Link reset dikirim via email
3. **Reset Password Page** - Form password baru dengan validasi
4. **Token Security** - Token expired setelah 1 jam
5. **Link di Login Page** - "Lupa Password?" di halaman login

---

## **Cara Setup (WAJIB!):**

### **1. Buat Tabel Database**
Jalankan sekali saja via browser:
```
http://localhost/FinalProject/setup_password_reset.php
```

**Apa yang terjadi:**
- Tabel `password_resets` dibuat otomatis
- Struktur: `id`, `email`, `token`, `created_at`

**Setelah selesai:**
- **HAPUS** file `setup_password_reset.php`

---

##  **Cara Testing:**

### **Test 1: Request Reset Password**

1. **Buka halaman login:**
   ```
   http://localhost/FinalProject/public/index.php
   ```

2. **Klik link "Lupa Password?"**

3. **Masukkan email user yang terdaftar:**
   - Contoh: `dudung@gmail.com`
   - Klik "Kirim Link Reset"

4. **Cek inbox email:**
   - Email dengan subject "Reset Password - TaskAcademy"
   - Isi: Link reset + warning expired 1 jam

5. **Klik link di email:**
   - Redirect ke halaman reset password

---

### **Test 2: Reset Password**

1. **Di halaman reset password:**
   - Masukkan password baru (min 6 karakter)
   - Konfirmasi password
   - Klik "Reset Password"

2. **Verifikasi:**
   - Muncul pesan sukses
   - Klik "Login Sekarang"

3. **Login dengan password baru:**
   - Email: `dudung@gmail.com`
   - Password: (password baru yang tadi dibuat)
   - Harus berhasil login

---

### **Test 3: Token Expiration**

1. **Request reset password**
2. **JANGAN klik link di email**
3. **Tunggu 1 jam** (atau ubah waktu di database)
4. **Klik link setelah 1 jam:**
   - Muncul error "Link tidak valid atau sudah kadaluarsa"
   - Harus request ulang

---

### **Test 4: Invalid Token**

1. **Copy link reset dari email**
2. **Ubah token di URL:**
   - Dari: `?token=abc123...`
   - Jadi: `?token=invalid123`
3. **Buka link yang sudah diubah:**
   - Muncul error "Link tidak valid"

---

### **Test 5: Email Tidak Terdaftar**

1. **Buka halaman lupa password**
2. **Masukkan email yang TIDAK terdaftar:**
   - Contoh: `emailpalsu@gmail.com`
3. **Klik kirim:**
   - Tetap muncul pesan sukses (security: jangan kasih tau email tidak ada)
   - Email TIDAK dikirim

---

## **Fitur Keamanan:**

### **1. Token Expiration**
- Token hanya berlaku **1 jam**
- Setelah itu harus request ulang

### **2. One-Time Use**
- Token langsung dihapus setelah dipakai
- Tidak bisa dipakai 2x

### **3. Password Validation**
- Minimal 6 karakter
- Harus konfirmasi password
- Password di-hash dengan `password_hash()`

### **4. Email Privacy**
- Tidak kasih tau apakah email terdaftar atau tidak
- Mencegah email enumeration attack

### **5. Old Token Cleanup**
- Token lama otomatis dihapus saat request baru
- Cegah spam token

---

##  **Email Template:**

Email yang dikirim berisi:
- ✅ Nama user
- ✅ Link reset password
- ✅ Warning expired 1 jam
- ✅ Instruksi kalau tidak request
- ✅ Link manual (kalau tombol tidak bisa diklik)
- ✅ Footer dengan branding

**Design:** Sama dengan email notifikasi tugas (Midnight Royal theme)

---

##  **Troubleshooting:**

### **Error: "Tabel password_resets tidak ada"**
- Jalankan `setup_password_reset.php`

### **Email tidak terkirim**
- Cek SMTP settings di `.env`
- Pastikan `SMTP_USER` dan `SMTP_PASS` benar
- Test dengan email lain

### **Link reset tidak bisa diklik**
- Copy link manual dari email
- Paste di browser

### **Token selalu expired**
- Cek waktu server (harus sama dengan waktu lokal)
- Cek query SQL: `DATE_SUB(NOW(), INTERVAL 1 HOUR)`

---

## **Database Schema:**

```sql
CREATE TABLE `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `email` (`email`),
    KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## **Checklist Presentasi:**

Saat demo ke dosen:

- [ ] Tunjukkan link "Lupa Password?" di login page
- [ ] Input email dan kirim
- [ ] Buka email di HP/laptop (live!)
- [ ] Klik link reset
- [ ] Ubah password
- [ ] Login dengan password baru
- [ ] **SUKSES!** 🎉

---

## **Nilai Plus untuk Dosen:**

1. **Security Best Practice** - Token expiration, one-time use
2. **User Experience** - Email notification, clear instructions
3. **Professional** - Email template yang rapi
4. **Complete Feature** - Tidak setengah-setengah

---

**Fitur Lupa Password SIAP DIPAKAI!** 

**Jangan lupa:**
1. Jalankan `setup_password_reset.php`
2. Hapus file setup setelah selesai
3. Test semua skenario di atas
4. Export database lagi (ada tabel baru)

**Good luck!** 
