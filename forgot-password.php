<?php
require_once 'database/dbconnection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $database->dbConnection();
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

        // PHPMailer setup
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com'; // update if needed
            $mail->Password = 'your_app_password_here'; // update with your Gmail app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('rennielsalazar948@gmail.com', 'Your Site Name');
            $mail->addAddress($email);

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Forgot Password</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background:#f2f2f2; display:flex; justify-content:center; align-items:center; height:100vh; }
        .container { background:white; padding:2rem 3rem; border-radius:12px; box-shadow:0 0 15px rgba(0,0,0,0.1); width:400px; }
        h2 { margin-bottom:20px; color:#333; }
        label { display:block; margin-top:10px; font-weight:bold; }
        input[type=email] { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #ccc; }
        button { margin-top:20px; background:#1e90ff; color:#fff; border:none; padding:10px 15px; width:100%; border-radius:6px; cursor:pointer; font-weight:bold; }
        .message { margin-top:15px; color:#555; }
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
