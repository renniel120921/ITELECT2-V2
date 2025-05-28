<?php
session_start();
require_once 'config/settings-configuration.php';
require_once 'database/dbconnection.php';

// Load Composer's autoloader (if you used composer)
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: forgot-password.php");
        exit;
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $_SESSION['error'] = "Invalid email address.";
        header("Location: forgot-password.php");
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $system = new SystemConfig();
    $stmt = $system->runQuery("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)
        ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires");

    $stmt->execute([
        ':email' => $email,
        ':token' => $token,
        ':expires' => $expires_at
    ]);

    $resetLink = "http://localhost/ITELECT2-V2/reset-password.php?token=$token";

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';            // Use Gmail SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rennielsalazar948@gmail.com';       // Your Gmail address
        $mail->Password   = 'gssm lvoy ssrf ozxw';         // Use App Password, NOT your Gmail password
        $mail->SMTPSecure = 'tls';                        // Encryption: tls or ssl
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('rennielsalazar948@gmail.com', 'Reset ka password boss?');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password';
        $mail->Body    = "Hi,<br><br>Click the link below to reset your password:<br>
                          <a href='$resetLink'>$resetLink</a><br><br>
                          This link expires in 1 hour.";

        $mail->send();
        $_SESSION['message'] = "Reset link has been sent to your email.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
    }

    header("Location: forgot-password.php");
    exit;
}
?>
