<?php
session_start();

require '../../../vendor/autoload.php';  // load Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once '../../../database/dbconnection.php';
include_once '../../../config/settings-configuration.php';


$database = new Database();
$conn = $database->dbConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn-forgot-password'])) {
    if (!$conn) die("Database connection failed.");

    $email = trim($_POST['email']);
    $csrf_token = $_POST['csrf_token'];

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Invalid CSRF token.");
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
        $insert->execute([
            ':email' => $email,
            ':token' => $token,
            ':expires' => $expires
        ]);

        $reset_link = "http://localhost/ITELECT2-V2/reset-password.php?token=$token";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com'; // Replace with your Gmail
            $mail->Password = 'aift rzhk xzkb irnj'; // Use your Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('rennielsalazar948@gmail.com', 'ITELECT2 Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body = "Click the link below to reset your password:<br><a href='$reset_link'>$reset_link</a>";

            $mail->send();
            echo "Reset link sent to your email.";
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No account found with that email.";
    }
}
