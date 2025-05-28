<?php
session_start();

include_once '../../../database/dbconnection.php';
include_once '../../../config/settings-configuration.php';

// Create PDO connection
$database = new Database();
$conn = $database->dbConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn-reset-password'])) {

    $csrf_token       = $_POST['csrf_token'] ?? '';
    $reset_token      = $_POST['reset_token'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // CSRF token check
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("⚠ Invalid CSRF token.");
    }

    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        die("⚠ Please fill in all password fields.");
    }

    if ($new_password !== $confirm_password) {
        die("⚠ Passwords do not match.");
    }

    if (strlen($new_password) < 8) {
        die("⚠ Password must be at least 8 characters long.");
    }

    try {
        // Check if reset token exists and is not expired
        $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = :token LIMIT 1");
        $stmt->execute([':token' => $reset_token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            die("⚠ Invalid or expired reset token.");
        }

        // Check expiration
        $expires_at = new DateTime($row['expires_at']);
        $now = new DateTime();
        if ($now > $expires_at) {
            die("⚠ Reset token has expired.");
        }

        $email = $row['email'];

        // Hash the new password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update user's password
        $update = $conn->prepare("UPDATE user SET password = :password WHERE email = :email");
        $update->execute([
            ':password' => $password_hash,
            ':email'    => $email
        ]);

        // Delete the used token
        $delete = $conn->prepare("DELETE FROM password_resets WHERE token = :token");
        $delete->execute([':token' => $reset_token]);

        echo "✅ Password successfully reset. You can now <a href='/ITELECT2-V2/login.php'>login</a>.";
        exit;

    } catch (PDOException $e) {
        die("❌ Error: " . $e->getMessage());
    }

} else {
    echo "⚠ Invalid request method.";
}
