<?php
session_start();
require 'vendor/autoload.php'; // If using Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $otp = rand(100000, 999999);
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_otp'] = $otp;

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rennielsalazar948@gmail.com'; // Your Gmail
        $mail->Password   = 'xiam wqyh hsrj pqcl';    // App password, not Gmail password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('rennielsalazar948@gmail.com', 'Reset ka password ya?');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body    = "<h3>Your OTP is: <strong>$otp</strong></h3><p>Please use it to reset your password.</p>";

        $mail->send();
        echo "<script>alert('OTP sent to $email'); window.location.href='reset-password.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            width: 350px;
        }
        input[type=email], input[type=submit] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        input[type=submit] {
            background: #007bff;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Forgot Password</h2>
        <label>Email Address</label>
        <input type="email" name="email" required placeholder="Enter your email">
        <input type="submit" value="Send OTP">
    </form>
</body>
</html>
