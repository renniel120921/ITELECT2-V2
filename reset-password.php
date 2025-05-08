<?php
    include_once 'reset-password.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/css/reset-pass.css">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>

    <form action="#" method="POST">
        <label for="new-password">New Password:</label><br>
        <input type="password" id="new-password" name="new_password" required><br><br>

        <label for="confirm-password">Confirm Password:</label><br>
        <input type="password" id="confirm-password" name="confirm_password" required><br><br>

        <button type="submit">Reset Password</button>
    </form>

    <p><a href="Index.php">Back to Login</a></p>
</body>
</html>
