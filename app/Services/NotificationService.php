<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class NotificationService
{
    private $pdo;
    private $mail;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->setupMailer();
    }

    private function setupMailer()
    {
        // 1. Pastikan Environment Variables Terload
        // Jika $_ENV kosong, kita load manual pake Dotenv
        if (empty($_ENV['SMTP_HOST'])) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();
        }

        $this->mail = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $this->mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            
            // Auto-detect encryption based on port
            $port = (int)($_ENV['SMTP_PORT'] ?? 587);
            $this->mail->Port = $port;
            
            if ($port === 465) {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $fromEmail = $_ENV['SMTP_USER'] ?? 'noreply@taskacademia.com';
            $fromName  = $_ENV['SMTP_FROM_NAME'] ?? 'TaskAcademia Notifier';
            
            $this->mail->setFrom($fromEmail, $fromName);
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';
            
            // Timeout settings for hosting stability
            $this->mail->Timeout = 30;
        } catch (Exception $e) {
            error_log("Mailer Setup Error: " . $e->getMessage());
        }
    }

    /**
     * Kirim email dan catat history-nya ke database
     * 
     * @param int $userId ID User penerima (untuk log)
     * @param string $toEmail Alamat email penerima
     * @param string $subject Subjek email
     * @param string $body Konten email (HTML)
     * @return bool True jika sukses, False jika gagal
     */
    public function sendEmail($userId, $toEmail, $subject, $body)
    {
        // 1. Catat Log (Status: Pending)
        // Kita simpan dulu di DB sebelum kirim, supaya kalau error kita punya jejaknya
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, message, channel, status) VALUES (?, ?, 'email', 'pending')");
        // Save a snippet of body or subject as message log
        $logMessage = "Subject: $subject | To: $toEmail"; 
        $stmt->execute([$userId, $logMessage]);
        $notificationId = $this->pdo->lastInsertId();

        try {
            // Reset recipients for batch processing
            $this->mail->clearAddresses();
            
            // 2. Send Email
            $this->mail->addAddress($toEmail);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->send();

            // 3. Update Log (Success)
            $update = $this->pdo->prepare("UPDATE notifications SET status='sent', sent_at=NOW() WHERE id=?");
            $update->execute([$notificationId]);

            return true;

        } catch (Exception $e) {
            // 4. Update Log (Failed)
            $error = $this->mail->ErrorInfo;
            $update = $this->pdo->prepare("UPDATE notifications SET status='failed', error_log=? WHERE id=?");
            $update->execute([$error, $notificationId]);
            
            return false;
        }
    }
}
