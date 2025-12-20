<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPmailer\PHPMailer\Exception;

$dotenv->safeLoad();
class EmailConfig
{
    public static function build()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');

        $mail = new PHPMailer(true);

        // Ambil dari ENV
        $host     = $_ENV['SMTP_HOST'] ?? '';
        $port     = $_ENV['SMTP_PORT'] ?? 587;
        $secure   = $_ENV['SMTP_SECURE'] ?? 'tls';
        $username = $_ENV['SMTP_USER'] ?? '';
        $password = $_ENV['SMTP_PASS'] ?? '';
        $from     = $_ENV['SMTP_FROM'] ?? $username;
        $fromName = $_ENV['SMTP_FROM_NAME'] ?? 'Notification Service';

        // Setup SMTP
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = $secure;
        $mail->Port       = $port;
        $mail->setFrom($from, $fromName);
        $mail->isHTML(true);

        return $mail;

        // jadi engke tinggal panggil2 wae di file notif+remender
    }
}
