<?php
require_once'database/dbconnection.php';

$msg = "";

if (!isset($_GET['token'])) {
    die("Invalid token.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM user WHERE token_code = :token AND reset_token_expiration > NOW()");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    die("Token expired or invalid.");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password != $confirm) {
        $msg = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE user SET password = :password, token_code = NULL, reset_token_expiration = NULL WHERE id = :id");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        $msg = "Password reset successful! <a href='login.php'>Login here</a>.";
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
            font-family: 'Segoe UI', sans-serif;
            background: #e9ecef;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .message {
            margin-top: 15px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <form method="POST">
        <label>New Password:</label>
        <input type="password" name="password" required>
        <label>Confirm Password:</label>
        <input type="password" name="confirm" required>
        <button type="submit">Reset Password</button>
    </form>
    <div class="message"><?= $msg ?></div>
</div>
</body>
</html>
