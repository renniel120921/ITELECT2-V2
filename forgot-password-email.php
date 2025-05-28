<?php
session_start();
require_once 'config/settings-configuration.php';
require_once 'database/dbconnection.php';
require 'vendor/autoload.php'; // for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: forgot-password.php");
    exit();
}

$email = $_POST['email'] ?? '';

if (empty($email)) {
    $_SESSION['error'] = "Email is required.";
    header("Location: forgot-password.php");
    exit();
}

// Check if the user exists
$systemConfig = new SystemConfig();
$stmt = $systemConfig->runQuery("SELECT * FROM user WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "No user found with that email.";
    header("Location: forgot-password.php");
    exit();
}

// Generate token and expiry
$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

// Insert token into password_resets
$insertStmt = $systemConfig->runQuery("
    INSERT INTO password_resets (email, token, expires_at)
    VALUES (:email, :token, :expires_at)
");
$insertStmt->execute([
    ':email' => $email,
    ':token' => $token,
    ':expires_at' => $expires_at
]);

// Send reset link email
$reset_link = "http://localhost/ITELECT2-V2/reset-password.php?token=" . urlencode($token);

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $systemConfig->getSmtpEmail();
    $mail->Password = $systemConfig->getSmtpPassword();
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom($systemConfig->getSmtpEmail(), 'Support');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Reset your password';
    $mail->Body = "Hi, <br><br>Click the link below to reset your password:<br><br>
        <a href='$reset_link'>$reset_link</a><br><br>
        This link will expire in 1 hour.";

    $mail->send();
    $_SESSION['message'] = "Reset link has been sent to your email.";
    header("Location: forgot-password.php");
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
    header("Location: forgot-password.php");
    exit();
}
