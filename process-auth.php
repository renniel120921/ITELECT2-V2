<?php
session_start();
require_once 'dashboard/admin/authentication/admin-class.php';

$admin = new ADMIN();

if (isset($_POST['btn-signup'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF Token (optional pero recommended)
    if (!isset($_SESSION["csrf_token"]) || $_SESSION["csrf_token"] !== $csrf_token) {
        die("Invalid CSRF token");
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Save data in session (temporary storage before finalizing registration)
    $_SESSION['temp_username'] = $username;
    $_SESSION['temp_email'] = $email;
    $_SESSION['temp_password'] = $password;
    $_SESSION['temp_otp'] = $otp;

    // Send the OTP
    if ($admin->sendOtp($otp, $email)) {
        // Redirect to OTP verification page
        header("Location: verify-otp.php");
        exit;
    } else {
        echo "Failed to send OTP. Please try again.";
    }
}

if (isset($_POST['btn-signin'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!isset($_SESSION["csrf_token"]) || $_SESSION["csrf_token"] !== $csrf_token) {
        die("Invalid CSRF token");
    }

    $admin->adminSignin($email, $password, $csrf_token);
}
?>
