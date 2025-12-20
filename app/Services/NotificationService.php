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
        $this->mail = new PHPMailer(true);
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USER'];
        $this->mail->Password   = $_ENV['SMTP_PASS'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $this->mail->Port       = $_ENV['SMTP_PORT'];
        $this->mail->setFrom($_ENV['SMTP_USER'], $_ENV['SMTP_FROM_NAME'] ?? 'TaskAcademia Notifier');
        $this->mail->isHTML(true);
    }

    /**
     * Send email and log to database
     */
    public function sendEmail($userId, $toEmail, $subject, $body)
    {
        // 1. Log Attempt (Pending)
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
