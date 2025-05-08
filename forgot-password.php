<?php
    include_once 'forgot-password.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="src/css/forgot.css">
    <title>Document</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form action="#" method="POST">
        <label for="#">Enter your email</label><br>
        <input type="email" id="email" name="email" required><br><br>
        <button type="submit">Send Reset Link</button>
    </form>
    <p><a href="#">Back to Login</a></p>

</body>
</html>
