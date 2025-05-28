<?php
session_start();
include_once '../../../database/dbconnection.php';
include_once '../../../config/settings-configuration.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn-forgot-password'])) {

    if (!$conn) {
        die("Database connection failed.");
    }

    $email = trim($_POST['email']);
    $csrf_token = $_POST['csrf_token'];

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Invalid CSRF token.");
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $email, $token, $expires);
        $insert->execute();

        $reset_link = "http://localhost/ITELECT2-V2/reset-password.php?token=$token";
        mail($email, "Reset Your Password", "Click this link to reset: $reset_link");

        echo "Reset link sent to your email.";
    } else {
        echo "No account found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>
