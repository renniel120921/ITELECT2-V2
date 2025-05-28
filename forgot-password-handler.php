<?php
session_start();
require_once 'database/dbconnection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $db->dbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-forgot-password'])) {

    // CSRF Token Check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header("Location: forgot-password.php");
        exit();
    }

    // Sanitize email input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Update token and expiry in DB
        $update = $conn->prepare("UPDATE user SET reset_token = :token, token_expiry = :expiry WHERE email = :email");
        $update->bindParam(":token", $token);
        $update->bindParam(":expiry", $expiry);
        $update->bindParam(":email", $email);
        $update->execute();

        // Email Sending
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'rennielsalazar948@gmail.com'; // 🔐 REPLACE
            $mail->Password   = 'gssm lvoy ssrf ozxw';    // 🔐 USE AN APP PASSWORD
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('rennielsalazar948@gmail.com', 'Reset ka Password');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';

            $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . urlencode($token);
            $mail->Body = "Hi,<br><br>Click the link below to reset your password:<br><br>
                           <a href='$resetUrl'>$resetUrl</a><br><br>This link will expire in 1 hour.";

            $mail->send();
            $_SESSION['message'] = "Reset link has been sent to your email address.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Email sending failed: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "No account found with that email address.";
    }

    header("Location: forgot-password.php");
    exit();
}
