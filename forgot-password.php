<?php
session_start();
require 'vendor/autoload.php'; // PHPMailer via Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Optional: check if email exists in your DB before proceeding
    // if (!emailExistsInDatabase($email)) {
    //     echo "<script>alert('Email not found'); window.location.href='forgot-password.php';</script>";
    //     exit;
    // }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.location.href='forgot-password.php';</script>";
        exit;
    }

    // Generate OTP and store in session
    $otp = random_int(100000, 999999); // More secure than rand()
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_otp'] = $otp;

    // Send OTP email
    $mail = new PHPMailer(true);
    try {
        // SMTP server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rennielsalazar948@gmail.com'; // Your Gmail
        $mail->Password   = 'xiam wqyh hsrj pqcl';         // App-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email setup
        $mail->setFrom('rennielsalazar948@gmail.com', 'Password Reset - Renniel');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Password Reset';
        $mail->Body    = "<h3>Hi there,</h3>
                          <p>You requested a password reset. Use the following OTP to continue:</p>
                          <h2>$otp</h2>
                          <p>This OTP is valid for this session only.</p>";

        $mail->send();
        echo "<script>alert('OTP sent to $email'); window.location.href='reset-password.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Failed to send OTP: {$mail->ErrorInfo}'); window.location.href='forgot-password.php';</script>";
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
