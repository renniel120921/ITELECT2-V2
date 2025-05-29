<?php
require 'dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE user SET token_code = :token, reset_token_expiration = :expires WHERE email = :email");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $resetLink = "http://yourdomain.com/reset-password.php?token=" . $token;
        echo "A reset link has been sent. (For demo: $resetLink)";
    } else {
        echo "No account found with that email.";
    }
}
?>

<form method="POST">
    <label>Email:</label>
    <input type="email" name="email" required>
    <button type="submit">Send Reset Link</button>
</form>
