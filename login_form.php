<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>

<h2>Login</h2>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlspecialchars($_SESSION['error']).'</p>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['message'])) {
    echo '<p style="color:green;">'.htmlspecialchars($_SESSION['message']).'</p>';
    unset($_SESSION['message']);
}
?>

<form method="POST" action="login.php">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required autofocus><br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>
