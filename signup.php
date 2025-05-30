<?php
session_start();
require_once 'config/db.php';      // your PDO connection
require_once 'config/email.php';   // the PHPMailer wrapper with sendOTPEmail()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (!$username || !$email || !$password) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: signup_form.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: signup_form.php");
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: signup_form.php");
        exit;
    }

    // Hash password securely
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Generate OTP & expiry (5 minutes from now)
    $otp = random_int(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Insert new user with status = 0 (not verified), token code empty
    $stmt = $pdo->prepare("INSERT INTO user (username, email, password, status, otp, otp_expiry, created_at) VALUES (?, ?, ?, 0, ?, ?, NOW())");
    $stmt->execute([$username, $email, $passwordHash, $otp, $otp_expiry]);

    $userId = $pdo->lastInsertId();

    // Send OTP email
    if (sendOTPEmail($email, $otp, $pdo)) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['message'] = "OTP sent to your email. Please verify.";
        header("Location: verify_otp.php");
        exit;
    } else {
        // If email fails, delete the user to avoid orphan unverified accounts
        $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$userId]);
        $_SESSION['error'] = "Failed to send OTP email. Try again later.";
        header("Location: signup_form.php");
        exit;
    }
} else {
    header("Location: signup_form.php");
    exit;
}
