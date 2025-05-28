<?php
session_start();
include_once '../../../database/dbconnection.php';
include_once '../../../config/settings-configuration.php';

// Create PDO connection from your class
$database = new Database();
$conn = $database->dbConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn-forgot-password'])) {

    if (!$conn) {
        die("Database connection failed.");
    }

    $email = trim($_POST['email']);
    $csrf_token = $_POST['csrf_token'];

    // CSRF token check
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

        // Insert reset record
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
        $insert->bindParam(':email', $email);
        $insert->bindParam(':token', $token);
        $insert->bindParam(':expires', $expires);
        $insert->execute();

        // Send reset email
        $reset_link = "http://localhost/ITELECT2-V2/reset-password.php?token=$token";
        $subject = "Reset Your Password";
        $message = "Click the link below to reset your password:\n\n$reset_link";
        $headers = "From: no-reply@itelect2.com";

        mail($email, $subject, $message, $headers);

        echo "Reset link sent to your email.";
    } else {
        echo "No account found with that email.";
    }
}
?>
