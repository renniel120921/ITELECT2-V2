<?php
session_start();
require 'config/settings-configuration.php'; // your PDO connection or mysqli setup
require 'vendor/autoload.php'; // PHPMailer autoload, adjust path if needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check request and button
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['btn-forgot-password'])) {
    header('Location: forgot-password.php');
    exit;
}

// CSRF protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

$email = trim($_POST['email'] ?? '');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Please enter a valid email address.');
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // To prevent email enumeration, just pretend it's successful
        echo "If this email exists in our system, a reset link has been sent.";
        exit;
    }

    // Generate token and expiry (e.g., 1 hour)
    $token = bin2hex(random_bytes(16));
    $expiry = date('Y-m-d H:i:s', time() + 3600);

    // Save token and expiry to database
    $update = $pdo->prepare("UPDATE user SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id");
    $update->execute([
        'token' => $token,
        'expiry' => $expiry,
        'id' => $user['id']
    ]);

    // Prepare reset link
    $resetLink = "https://yourdomain.com/forgot-reset-password.php?token=" . $token;

    // Send email with PHPMailer
    $mail = new PHPMailer(true);

    // Server settings (adjust accordingly)
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com'; // your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'rennielsalazar948@example.com'; // SMTP username
    $mail->Password = 'gssm lvoy ssrf ozxw';    // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('no-reply@yourdomain.com', 'Your Site Name');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = "
        <p>Hi,</p>
        <p>You requested a password reset. Click the link below to reset your password:</p>
        <p><a href='{$resetLink}'>Reset Password</a></p>
        <p>This link will expire in 1 hour.</p>
        <p>If you did not request this, please ignore this email.</p>
    ";

    $mail->send();

    echo "If this email exists in our system, a reset link has been sent.";
} catch (Exception $e) {
    // Log error if needed: $e->getMessage()
    die('Something went wrong. Please try again later.');
}
