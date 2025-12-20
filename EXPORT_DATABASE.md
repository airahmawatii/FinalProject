# 📦 Cara Export Database

## **Metode 1: Via phpMyAdmin (RECOMMENDED)**

### Langkah-langkah:

1. **Buka phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Pilih Database**
   - Klik database `fp` di sidebar kiri

3. **Export**
   - Klik tab **"Export"** di menu atas
   - Method: **Quick** (atau Custom jika mau setting detail)
   - Format: **SQL**
   - Klik tombol **"Go"**

4. **Save File**
   - Browser akan download file `fp.sql`
   - **Rename** file jadi `database_backup.sql`
   - **Pindahkan** ke folder:
     ```
     C:\xampp\htdocs\FinalProject\setup_database\database_backup.sql
     ```

5. **Verifikasi**
   - Cek ukuran file (harus > 10 KB)
   - Buka file dengan Notepad, pastikan ada SQL commands

---

## **Metode 2: Via Command Line (ALTERNATIF)**

### Jika MySQL sudah running:

1. **Buka Command Prompt (CMD) as Administrator**

2. **Jalankan command:**
   ```bash
   cd C:\xampp\mysql\bin
   mysqldump -u root fp > C:\xampp\htdocs\FinalProject\setup_database\database_backup.sql
   ```

3. **Cek hasilnya:**
   ```bash
   dir C:\xampp\htdocs\FinalProject\setup_database\database_backup.sql
   ```

---

## **Troubleshooting**

### Error: "MySQL not running"
- Buka XAMPP Control Panel
- Start MySQL
- Coba lagi

### Error: "Access denied"
- Pastikan user `root` tidak pakai password
- Atau tambahkan `-p` di command: `mysqldump -u root -p fp > ...`

### File terlalu kecil (< 5 KB)
- Database mungkin kosong
- Cek di phpMyAdmin apakah ada data di tabel

---

## **Setelah Export Berhasil:**

✅ File `database_backup.sql` sudah ada di `setup_database/`  
✅ Hapus file cleanup (sudah otomatis)  
✅ README.md sudah update nama kelompok  
✅ Buka `TESTING_CHECKLIST.md` untuk final testing  

**SISTEM SIAP 100%!** 🎉
