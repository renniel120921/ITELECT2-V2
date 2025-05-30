<?php
require_once __DIR__ . '/dashboard/admin/authentication/admin-class.php';;
$admin = new Admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($admin->register($username, $email, $password)) {
        // redirect to verify-otp.php
        header("Location: verify-otp.php?email=" . urlencode($email));
        exit();
    } else {
        $error = "Registration failed. Email might already be used.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sign Up</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f2f2f2;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        background: white;
        padding: 2rem 3rem;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        width: 350px;
    }
    h2 {
        text-align: center;
        margin-bottom: 1.5rem;
        color: #333;
    }
    label {
        display: block;
        margin-bottom: 0.4rem;
        color: #555;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 0.6rem;
        margin-bottom: 1.2rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1rem;
    }
    button {
        width: 100%;
        padding: 0.7rem;
        background-color: #007bff;
        border: none;
        color: white;
        font-size: 1.1rem;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #0056b3;
    }
    .error {
        background: #f8d7da;
        color: #842029;
        padding: 0.7rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        border: 1px solid #f5c2c7;
        font-size: 0.9rem;
        text-align: center;
    }
    .login-link {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #555;
    }
    .login-link a {
        color: #007bff;
        text-decoration: none;
    }
    .login-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Create Account</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required />

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required />

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required />

        <button type="submit">Sign Up</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Log in here</a>
    </div>
</div>
</body>
</html>
