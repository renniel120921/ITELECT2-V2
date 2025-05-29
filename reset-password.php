<?php
require_once 'database/dbconnection.php';

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM user WHERE token_code = :token AND reset_token_expiration > NOW()");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    die("Invalid or expired token.");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password != $confirm) {
        echo "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE user SET password = :password, token_code = NULL, reset_token_expiration = NULL WHERE id = :id");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        echo "Password has been reset. You can now <a href='login.php'>login</a>.";
    }
}
?>

<form method="POST">
    <label>New Password:</label>
    <input type="password" name="password" required>
    <label>Confirm Password:</label>
    <input type="password" name="confirm" required>
    <button type="submit">Reset Password</button>
</form>
