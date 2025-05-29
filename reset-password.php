<?php
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection();

$msg = "";
$success = false;

// 1. Token check
if (!isset($_GET['token']) || empty(trim($_GET['token']))) {
    die("Invalid request.");
}

$token = trim($_GET['token']);

// 2. Find user by token
$stmt = $conn->prepare("SELECT * FROM user WHERE tokencode = :token");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die("Link is expired or invalid.");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // TODO: Implement CSRF token verification here for security

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

        try {
            // Start transaction for safety
            $conn->beginTransaction();

            // 1. Clear the tokencode in user table
            $clearToken = $conn->prepare("UPDATE user SET tokencode = NULL WHERE id = :id");
            $clearToken->bindParam(':id', $user['id']);
            $clearToken->execute();

            // 2. Check if email exists in password_resets table
            $checkExist = $conn->prepare("SELECT * FROM password_resets WHERE email = :email");
            $checkExist->bindParam(':email', $user['email']);
            $checkExist->execute();

            if ($checkExist->rowCount() > 0) {
                // Update existing record
                $updateReset = $conn->prepare("UPDATE password_resets SET password = :password, updated_at = NOW() WHERE email = :email");
                $updateReset->bindParam(':password', $hashedPassword);
                $updateReset->bindParam(':email', $user['email']);
                $updateReset->execute();
            } else {
                // Insert new record
                $insertReset = $conn->prepare("INSERT INTO password_resets (email, password) VALUES (:email, :password)");
                $insertReset->bindParam(':email', $user['email']);
                $insertReset->bindParam(':password', $hashedPassword);
                $insertReset->execute();
            }

            $conn->commit();

            $msg = "Password reset successful! <a href='login.php'>Login here</a>.";
            $success = true;

            // Redirect after 3 seconds
            header("refresh:3;url=login.php");
        } catch (Exception $e) {
            $conn->rollBack();
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
