<?php
session_start();
require_once 'config/db.php';      // your PDO connection

if (!isset($_SESSION['user_id'])) {
    // No user to verify, redirect to signup or login
    header("Location: signup_form.php");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputOtp = trim($_POST['otp'] ?? '');

    if (!$inputOtp) {
        $_SESSION['error'] = "Please enter the OTP.";
        header("Location: verify_otp_form.php");
        exit;
    }

    // Fetch user OTP and expiry from DB
    $stmt = $pdo->prepare("SELECT otp, otp_expiry FROM user WHERE id = ? AND status = 0");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "Invalid request or already verified.";
        header("Location: signup_form.php");
        exit;
    }

    // Check expiry
    if (new DateTime() > new DateTime($user['otp_expiry'])) {
        $_SESSION['error'] = "OTP expired. Please sign up again.";
        // Delete unverified user record to force fresh signup
        $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$userId]);
        session_destroy();
        header("Location: signup_form.php");
        exit;
    }

    // Check OTP match
    if ($inputOtp == $user['otp']) {
        // Update user status to active (1), clear otp fields
        $stmt = $pdo->prepare("UPDATE user SET status = 1, otp = NULL, otp_expiry = NULL WHERE id = ?");
        $stmt->execute([$userId]);

        $_SESSION['message'] = "Your account has been verified! You can now log in.";
        unset($_SESSION['user_id']);  // clear signup session

        header("Location: login_form.php");
        exit;
    } else {
        $_SESSION['error'] = "Incorrect OTP. Try again.";
        header("Location: verify_otp_form.php");
        exit;
    }
} else {
    header("Location: verify_otp_form.php");
    exit;
}
