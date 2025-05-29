<?php
require_once 'database/dbconnection.php';
require 'vendor/autoload.php'; // Ito lang ang kailangan
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE user SET tokencode = :token, reset_token_expiration = :expires WHERE email = :email");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $resetLink = "http://localhost/ITELECT2-V2/reset-password.php?token=" . $token;

        // === PHPMailer ===
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com';       // 🔴 Palitan mo ito
            $mail->Password = 'capz hnue qqiz ndnd';         // 🔴 Gamitin mo ang App Password ng Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('rennielsalaazar948@gmail.com', 'Reset Password ka ya??');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click the link below to reset your password:<br><br>
                           <a href='$resetLink'>$resetLink</a><br><br>
                           This link will expire in 1 hour.";

            $mail->send();
            $msg = "Reset link sent to your email.";
        } catch (Exception $e) {
            $msg = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $msg = "No account found with that email.";
    }
}
?>
