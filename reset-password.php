<?php
require_once 'database/dbconnection.php';

$db = new Database();
$conn = $db->dbConnection();

$msg = "";
$success = false;

// Basic CSRF token for form protection (optional but recommended)
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_GET['token']) || empty(trim($_GET['token']))) {
    die("Invalid request.");
}

$token = trim($_GET['token']);

// Optional: Validate token format (assuming it's a 64-char hex string)
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    die("Invalid reset link format.");
}

// Look up token from password_resets with join on user
$stmt = $conn->prepare("
    SELECT pr.*, u.email, u.id AS user_id
    FROM password_resets pr
    INNER JOIN user u ON u.id = pr.user_id
    WHERE pr.token = :token AND pr.expires_at > NOW() AND pr.used = 0
");
$stmt->bindParam(':token', $token, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die("This reset link is invalid or expired.");
}

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (empty($password) || empty($confirm)) {
        $msg = "Please fill in all fields.";
    } elseif ($password !== $confirm) {
        $msg = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $msg = "Password must be at least 8 characters.";
    }
    // Optional: add password complexity check (comment out if you don't want)
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
        $msg = "Password must contain at least one uppercase letter, one lowercase letter, and one digit.";
    }
    else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $conn->beginTransaction();

            // Update user's password
            $update = $conn->prepare("UPDATE user SET password = :password WHERE id = :id");
            $update->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $update->bindParam(':id', $data['user_id'], PDO::PARAM_INT);
            $update->execute();

            // Mark the reset token as used
            $markUsed = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = :id");
            $markUsed->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $markUsed->execute();

            $conn->commit();

            $msg = "Password reset successful! Redirecting to login...";
            $success = true;

            // Clear CSRF token after success
            unset($_SESSION['csrf_token']);

            header("refresh:3;url=login.php");
            exit;
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
            color: <?php echo $success ? '#28a745' : '#dc3545'; ?>;
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
        <form method="POST" autocomplete="off" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="password">New Password:</label>
            <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" />
            <label for="confirm">Confirm Password:</label>
            <input id="confirm" type="password" name="confirm" required minlength="8" autocomplete="new-password" />
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
        <div class="message"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
</div>
</body>
</html>
