<?php
require_once 'database/dbconnection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database();
$conn = $database->dbConnection();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));

        // Update the token only (no expiration)
        $update = $conn->prepare("
            UPDATE user
            SET tokencode = :token
            WHERE email = :email
        ");
        $update->bindParam(':token', $token);
        $update->bindParam(':email', $email);
        $update->execute();

        // Link to reset password
        $resetLink = "http://localhost/ITELECT2-V2/reset-password.php?token=" . urlencode($token);

        // PHPMailer setup
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com'; // use actual email
            $mail->Password = 'capz hnue qqiz ndnd'; // use actual app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('rennielsalazar948@gmail.com', 'Password Reset');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body = "
                <p>Hi there,</p>
                <p>You requested to reset your password. Click the link below to proceed:</p>
                <p><a href='{$resetLink}'>Reset Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
            ";

            $mail->send();
            $msg = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $msg = "Failed to send email. Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
        }
    } else {
        $msg = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type=email] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background: #1e90ff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .message {
            margin-top: 15px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="POST">
            <label for="email">Enter your email:</label>
            <input type="email" name="email" required />
            <button type="submit">Send Reset Link</button>
        </form>
        <div class="message"><?= htmlspecialchars($msg) ?></div>
    </div>
</body>
</html>
