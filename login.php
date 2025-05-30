<?php
session_start();
require 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/user/index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ? AND status = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Log login activity
        $stmt_log = $pdo->prepare("INSERT INTO logs (user_id, activity, created_at) VALUES (?, ?, NOW())");
        $stmt_log->execute([$user['id'], 'Logged in']);

        header("Location: dashboard/user/index.php");
        exit;
    } else {
        $error = "Invalid email or password, or your account is not verified.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
<h2>Login</h2>
<?php
if (isset($_GET['verified'])) {
    echo "<p style='color:green;'>Account verified! Please login.</p>";
}
if ($error) echo "<p style='color:red;'>$error</p>";
?>
<form method="post" action="">
    Email: <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"><br><br>
    Password: <input type="password" name="password"><br><br>
    <button type="submit">Login</button>
</form>
</body>
</html>
