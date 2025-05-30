<?php
session_start();
require_once 'dashboard/admin/authentication/admin-class.php';

// Check if session vars exist
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_otp'])) {
    echo "<script>alert('Invalid session. Start the reset process again.'); window.location.href='forgot-password.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($otp !== $_SESSION['reset_otp']) {
        echo "<script>alert('Invalid OTP'); window.location.href='reset-password.php';</script>";
        exit;
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.location.href='reset-password.php';</script>";
        exit;
    }

    try {
        $admin = new ADMIN();
        $conn = $admin->dbConnection();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE user SET password = :password WHERE email = :email");
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $_SESSION['reset_email']);
        $stmt->execute();

        // Clean up
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_otp']);

        echo "<script>alert('Password has been reset!'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
    }
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
