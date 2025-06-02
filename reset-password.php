<?php
session_start();
require_once 'database/dbconnection.php';

$config = [
    'host'     => 'localhost',
    'port'     => '3306',
    'dbname'   => 'itelect2',
    'username' => 'root',
    'password' => ''
];

$db = (new Database($config))->dbConnection();

$token = $_GET['token'] ?? '';
$message = "";
$success = false;

// Basic token validation
if (!$token) {
    exit("⛔ Invalid or missing password reset token.");
}

// Validate token and check expiry
$stmt = $db->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset || strtotime($reset['expires_at']) < time()) {
    exit("⛔ Reset token is invalid or has expired.");
}

// CSRF token generation and validation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "⛔ Invalid request. Please refresh the page and try again.";
    } else {
        $new_password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (empty($new_password) || empty($confirm_password)) {
            $message = "⚠️ Please fill out both password fields.";
        } elseif ($new_password !== $confirm_password) {
            $message = "❌ Passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $message = "⚠️ Password must be at least 8 characters.";
        } else {
            // Fetch current password hash
            $stmt = $db->prepare("SELECT password FROM user WHERE id = ?");
            $stmt->execute([$reset['user_id']]);
            $user = $stmt->fetch();

            if (!$user) {
                $message = "⛔ User not found.";
            } elseif (password_verify($new_password, $user['password'])) {
                $message = "⚠️ New password must be different from the old password.";
            } else {
                // Update password
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $db->prepare("UPDATE user SET password = ? WHERE id = ?");
                $update->execute([$new_password_hashed, $reset['user_id']]);

                // Remove used token
                $del = $db->prepare("DELETE FROM password_resets WHERE token = ?");
                $del->execute([$token]);

                $message = "✅ Password reset successful! <a href='login.php'>Login now</a>";
                $success = true;

                // Invalidate CSRF token after success
                unset($_SESSION['csrf_token']);
            }
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
        background: linear-gradient(135deg, #1f1f1f, #333);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #f2f2f2;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background-color: #2a2a2a;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.05);
        width: 400px;
        box-sizing: border-box;
    }
    h2 {
        text-align: center;
        color: #00bcd4;
        margin-bottom: 25px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
    }
    input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: none;
        border-radius: 8px;
        background: #444;
        color: #fff;
        font-size: 16px;
        box-sizing: border-box;
    }
    button {
        width: 100%;
        padding: 12px;
        background-color: #00bcd4;
        border: none;
        border-radius: 8px;
        color: #000;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        font-size: 16px;
    }
    button:hover {
        background-color: #0097a7;
    }
    button:disabled {
        background-color: #666;
        cursor: not-allowed;
    }
    .message {
        margin-top: 20px;
        font-size: 14px;
        background-color: #444;
        padding: 15px;
        border-radius: 8px;
        word-break: break-word;
    }
    a {
        color: #00bcd4;
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Reset Your Password</h2>

    <?php if ($success): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php else: ?>
        <form method="POST" autocomplete="off" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" required minlength="8" />

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required minlength="8" />

            <button type="submit">Reset Password</button>
        </form>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
