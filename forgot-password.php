<?php
require_once 'database/dbconnection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database();
$conn = $database->dbConnection();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if ($email) {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));

            // Update token in DB
            $update = $conn->prepare("
                UPDATE user
                SET tokencode = :token
                WHERE email = :email
            ");
            $update->bindParam(':token', $token);
            $update->bindParam(':email', $email);
            $update->execute();

            $resetLink = "http://localhost/ITELECT2-V2/reset-password.php?token=" . urlencode($token);

            // Mailer setup
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'rennielsalazar948@gmail.com'; // move to ENV
                $mail->Password = 'capz hnue qqiz ndnd';         // move to ENV
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('rennielsalazar948@gmail.com', 'SM Mall Support');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password';
                $mail->Body = "
                    <p>Hello,</p>
                    <p>You requested a password reset. Click below to reset it:</p>
                    <p><a href='{$resetLink}'>Reset Password</a></p>
                    <p>If you didn’t request this, you can safely ignore this email.</p>
                ";

                $mail->send();
                $msg = "<span style='color: green;'>A password reset link has been sent to your email.</span>";
            } catch (Exception $e) {
                $msg = "<span style='color: red;'>Mailer Error: " . htmlspecialchars($mail->ErrorInfo) . "</span>";
            }
        } else {
            $msg = "<span style='color: red;'>No account found with that email address.</span>";
        }
    } else {
        $msg = "<span style='color: red;'>Please enter a valid email address.</span>";
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
            font-family: 'Segoe UI', sans-serif;
            background: #eef2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        input[type=email] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }
        button {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .message {
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="POST" novalidate>
            <label for="email">Enter your email:</label>
            <input type="email" name="email" required placeholder="you@example.com" />
            <button type="submit">Send Reset Link</button>
        </form>
        <div class="message"><?= $msg ?></div>
    </div>
</body>
</html>
