<?php
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection();

$msg = "";

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid token.");
}

$token = $_GET['token'];

// Fetch user by token only
$stmt = $conn->prepare("SELECT * FROM user WHERE tokencode = :token");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    die("Token expired or invalid.");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check token expiration in PHP
if (strtotime($user['reset_token_expiration']) < time()) {
    die("Token expired or invalid.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password !== $confirm) {
        $msg = "<span style='color:red;'>Passwords do not match.</span>";
    } elseif (strlen($password) < 8) {
        $msg = "<span style='color:red;'>Password must be at least 8 characters.</span>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE user SET password = :password, tokencode = NULL, reset_token_expiration = NULL WHERE id = :id");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        $msg = "<span style='color:green;'>Password reset successful! <a href='login.php'>Login here</a>.</span>";
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
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #28a745;
            text-align: center;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #218838;
        }
        .message {
            margin-top: 15px;
            font-size: 0.95rem;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <form method="POST" autocomplete="off">
        <label>New Password:</label>
        <input type="password" name="password" required minlength="8" autocomplete="new-password">
        <label>Confirm Password:</label>
        <input type="password" name="confirm" required minlength="8" autocomplete="new-password">
        <button type="submit">Reset Password</button>
    </form>
    <div class="message"><?= $msg ?></div>
</div>
</body>
</html>
