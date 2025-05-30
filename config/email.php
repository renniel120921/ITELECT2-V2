<?php
// config/email.php

require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Get email config from database
 * @param PDO $pdo
 * @return array|false
 */
function getEmailConfig(PDO $pdo) {
    $stmt = $pdo->query("SELECT * FROM email_config LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Send OTP email using PHPMailer and Gmail SMTP
 * @param string $to Recipient email
 * @param string $otp OTP code to send
 * @param PDO $pdo Database connection to fetch SMTP config
 * @return bool True if sent, false if error
 */
function sendOTPEmail(string $to, string $otp, PDO $pdo): bool {
    $config = getEmailConfig($pdo);
    if (!$config) {
        error_log('Email config not found in database.');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['rennielsalazar948@gmail.com'];      // your gmail email
        $mail->Password   = $config['urbd rpri htqg alrz'];   // your gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom($config['email'], 'Your App Name');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is <b>{$otp}</b>. It expires in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
