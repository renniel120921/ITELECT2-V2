<?php
session_start();
require_once 'config/dashboard/database/dbconnection.php';
 // make sure this path is correct

// Check CSRF token if you're using it (optional but recommended)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $email = trim($_POST['email']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format.');
    }

    // DB connection
    $database = new Database();
    $conn = $database->dbConnection();

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Insert or update into password_resets
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at)
                                  VALUES (:email, :token, :expires)
                                  ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires");
        $insert->bindParam(':email', $email);
        $insert->bindParam(':token', $token);
        $insert->bindParam(':expires', $expires);
        $insert->execute();

        // Send email with PHPMailer (code not included here yet)
        // ...

        echo "Password reset link has been sent to your email.";
    } else {
        echo "Email not found.";
    }
}
?>
