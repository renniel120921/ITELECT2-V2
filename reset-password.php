<?php
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection();

$msg = "";
$success = false;

if (!isset($_GET['token']) || empty(trim($_GET['token']))) {
    die("Invalid request.");
}

$token = trim($_GET['token']);

// 1. Look up token from password_resets
$stmt = $conn->prepare("
    SELECT pr.*, u.email, u.id AS user_id
    FROM password_resets pr
    INNER JOIN user u ON u.id = pr.user_id
    WHERE pr.token = :token AND pr.expires_at > NOW() AND pr.used = 0
");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die("This reset link is invalid or expired.");
}

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (empty($password) || empty($confirm)) {
        $msg = "Please fill in all fields.";
    } elseif ($password !== $confirm) {
        $msg = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $msg = "Password must be at least 8 characters.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 2. Update user's password
        $update = $conn->prepare("UPDATE user SET password = :password WHERE id = :id");
        $update->bindParam(':password', $hashedPassword);
        $update->bindParam(':id', $data['user_id']);

        // 3. Mark the reset token as used
        $markUsed = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = :id");
        $markUsed->bindParam(':id', $data['id']);

        if ($update->execute() && $markUsed->execute()) {
            $msg = "Password reset successful! <a href='login.php'>Login here</a>.";
            $success = true;
            header("refresh:3;url=login.php");
        } else {
            $msg = "Something went wrong. Please try again.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 400px;
            box-sizing: border-box;
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
            box-sizing: border-box;
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
            color: <?= $success ? 'green' : 'red' ?>;
        }
        a {
            color: #1e90ff;
            font-weight: bold;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <?php if (!$success): ?>
        <form method="POST" autocomplete="off">
            <label>New Password:</label>
            <input type="password" name="password" required minlength="8" autocomplete="new-password" />
            <label>Confirm Password:</label>
            <input type="password" name="confirm" required minlength="8" autocomplete="new-password" />
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
        <div class="message"><?= $msg ?></div>
    <?php endif; ?>
</div>
</body>
</html>
