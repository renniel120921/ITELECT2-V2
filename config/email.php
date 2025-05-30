<?php
// config/email.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path_to_phpmailer/Exception.php';
require 'path_to_phpmailer/PHPMailer.php';
require 'path_to_phpmailer/SMTP.php';

// Get email config from your database email_config table
function getEmailConfig($pdo) {
    $stmt = $pdo->query("SELECT * FROM email_config LIMIT 1");
    return $stmt->fetch();
}

function sendOTPEmail($to, $otp, $pdo) {
    $config = getEmailConfig($pdo);
    if (!$config) return false;

    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['rennielsalazar948@gmail.com']; // your gmail from db
        $mail->Password   = $config['urbd rpri htqg alrz']; // app password from db
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom($config['email'], 'Your App Name');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is <b>$otp</b>. It expires in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
