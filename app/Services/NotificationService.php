<?php
/**
 * NotificationService - Si Kurir Email Pintar
 * 
 * Class ini bertugas mengirim email (pengingat, notifikasi, dll) menggunakan library PHPMailer.
 * Keunggulannya: Setiap email yang dikirim dicatat di database agar kita bisa melacak
 * mana yang sukses dan mana yang gagal.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class NotificationService
{
    private $pdo;   // Koneksi Database
    private $mail;  // Objek PHPMailer

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        // Langsung siapkan mesin pengirim email saat class ini dipanggil
        $this->setupMailer();
    }

    /**
     * Konfigurasi Mesin Pengirim Email (SMTP)
     * Mengambil data dari file .env agar rahasia (password email) tetap aman.
     */
    private function setupMailer()
    {
        // Pastikan variabel lingkungan (.env) sudah dimuat
        if (empty($_ENV['SMTP_HOST'])) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();
        }

        $this->mail = new PHPMailer(true);
        
        try {
            // Pengaturan Server SMTP (misal: Gmail atau SMTP Hosting)
            $this->mail->isSMTP();
            $this->mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $this->mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            
            // Deteksi keamanan otomatis berdasarkan Port (465 atau 587)
            $port = (int)($_ENV['SMTP_PORT'] ?? 587);
            $this->mail->Port = $port;
            
            if ($port === 465) {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Atur Alamat Pengirim & Nama Pengirim
            $fromEmail = $_ENV['SMTP_USER'] ?? 'noreply@taskacademia.com';
            $fromName  = $_ENV['SMTP_FROM_NAME'] ?? 'TaskAcademia Notifier';
            
            $this->mail->setFrom($fromEmail, $fromName);
            $this->mail->isHTML(true); // Biar bisa kirim email desain cantik (HTML)
            $this->mail->CharSet = 'UTF-8';
            
            // Atur batas waktu tunggu agar server tidak hang
            $this->mail->Timeout = 30;
        } catch (Exception $e) {
            // Catat ke log server jika settingan email salah
            error_log("Gagal Menyiapkan Mailer: " . $e->getMessage());
        }
    }

    /**
     * Fungsi Utama: Kirim Email & Catat ke Database.
     * 
     * @param int $userId Target User
     * @param string $toEmail Alamat email tujuan
     * @param string $subject Judul email
     * @param string $body Isi email (HTML)
     * @param int|null $taskId (Opsional) ID Tugas yang berkaitan
     * @param string|null $type (Opsional) Tipe notifikasi (H-1 / Hari H)
     */
    public function sendEmail($userId, $toEmail, $subject, $body, $taskId = null, $type = null)
    {
        // 1. CATAT LOG AWAL: Simpan dulu ke database dengan status 'pending'
        // Langkah ini penting: Jika server mati tiba-tiba, kita tahu email mana yang belum terkirim.
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, task_id, message, channel, type, status) VALUES (?, ?, ?, 'email', ?, 'pending')");
        $logMessage = "Subject: $subject | To: $toEmail"; 
        $stmt->execute([$userId, $taskId, $logMessage, $type]);
        $notificationId = $this->pdo->lastInsertId();

        try {
            // Bersihkan daftar penerima sebelumnya (mencegah salah kirim pada proses batch)
            $this->mail->clearAddresses();
            
            // 2. PROSES PENGIRIMAN
            $this->mail->addAddress($toEmail);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->send();

            // 3. UPDATE LOG SUKSES: Tandai di DB bahwa email sudah 'sent'
            $update = $this->pdo->prepare("UPDATE notifications SET status='sent', sent_at=NOW() WHERE id=?");
            $update->execute([$notificationId]);

            return true;

        } catch (Exception $e) {
            // 4. UPDATE LOG GAGAL: Catat alasan kenapa gagal (misal: email salah atau server mati)
            $error = $this->mail->ErrorInfo;
            $update = $this->pdo->prepare("UPDATE notifications SET status='failed', error_log=? WHERE id=?");
            $update->execute([$error, $notificationId]);
            
            return false;
        }
    }
}
