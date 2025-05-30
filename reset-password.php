<?php
session_start();
require_once 'dashboard/admin/authentication/admin-class.php';

// Debug helper (remove in production)
function debugSession() {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if OTP and email session data exist
    if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email'])) {
        echo "<script>alert('Session expired or invalid request. Try again.'); window.location.href='forgot-password.php';</script>";
        exit;
    }

    if ($otp !== strval($_SESSION['reset_otp'])) {
        echo "<script>alert('Invalid OTP'); window.location.href='reset-password.php';</script>";
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.location.href='reset-password.php';</script>";
        exit;
    }

    // Update password
    $admin = new ADMIN();
    $hashed = md5($new_password); // Consider using password_hash() instead
    $stmt = $admin->runQuery("UPDATE user SET password = :password WHERE email = :email");
    $stmt->execute([
        ':password' => $hashed,
        ':email' => $_SESSION['reset_email']
    ]);

    // Clear session values
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_otp']);

    echo "<script>alert('Password has been reset!'); window.location.href='index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial;
            background: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: white;
            padding: 30px;
            width: 400px;
            border-radius: 10px;
            box-shadow: 0 0 10px #aaa;
        }
        input[type=text], input[type=password], input[type=submit] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type=submit] {
            background: #28a745;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Reset Your Password</h2>
        <label>OTP Code</label>
        <input type="text" name="otp" required placeholder="Enter OTP sent to email">
        <label>New Password</label>
        <input type="password" name="password" required placeholder="Enter new password">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Confirm new password">
        <input type="submit" value="Reset Password">
    </form>
</body>
</html>
