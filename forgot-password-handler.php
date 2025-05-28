<?php
session_start();
require_once 'database/dbconnection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $db->dbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn-forgot-password'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Delete any existing reset tokens for this email
        $del = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
        $del->bindParam(":email", $email);
        $del->execute();

        // Insert new token
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, token_expiry) VALUES (:email, :token, :expiry)");
        $insert->bindParam(":email", $email);
        $insert->bindParam(":token", $token);
        $insert->bindParam(":expiry", $expiry);
        $insert->execute();

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com';
            $mail->Password = 'gssm lvoy ssrf ozxw';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('renielsalazar@gmail.com', 'Your System Name');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Link';
            $mail->Body = "Click the link below to reset your password:<br><br>
                <a href='http://yourdomain.com/reset-password.php?token=$token'>Reset Password</a><br><br>
                This link will expire in 1 hour.";

            $mail->send();
            $_SESSION['message'] = "Reset link sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send email. {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "Email not found.";
    }

    header("Location: forgot-password.php");
    exit();
}
