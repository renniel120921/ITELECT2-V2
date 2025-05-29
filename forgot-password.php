<?php
require_once 'database/dbconnection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database();
$conn = $database->dbConnection();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));

        // Save the token
        $update = $conn->prepare("UPDATE user SET tokencode = :token WHERE email = :email");
        $update->bindParam(':token', $token);
        $update->bindParam(':email', $email);
        $update->execute();

        // Reset link
        $resetLink = "http://localhost/ITELECT2-V2/reset-password.php?token=" . urlencode($token);

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'rennielsalazar948@gmail.com';
            $mail->Password = 'capz hnue qqiz ndnd'; // Replace with secure app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('rennielsalazar948@gmail.com', 'Password Reset');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body = "
                <p>Hi,</p>
                <p>You requested a password reset. Click the link below to reset your password:</p>
                <p><a href='{$resetLink}'>Reset Password</a></p>
                <p>If you didn't request this, you can ignore this message.</p>
            ";

            $mail->send();
            $msg = "<span style='color:green;'>A reset link has been sent to your email.</span>";
        } catch (Exception $e) {
            $msg = "<span style='color:red;'>Failed to send email: " . htmlspecialchars($mail->ErrorInfo) . "</span>";
        }
    } else {
        $msg = "<span style='color:red;'>No account found with that email.</span>";
    }
}
?>
