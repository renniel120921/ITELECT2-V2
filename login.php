<?php
require_once 'admin-class.php';
$admin = new Admin();

$error = '';
$success = '';

// Show success message if redirected after OTP verification
if (isset($_GET['verified']) && $_GET['verified'] == '1') {
    $success = "Account successfully verified. You can now log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($admin->login($email, $password)) { // Assuming you have a login method
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login</title>
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
        box-sizing: border-box;
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
    .success {
        background: #d4edda;
        color: #155724;
        padding: 0.7rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        border: 1px solid #c3e6cb;
        font-size: 0.9rem;
        text-align: center;
    }
    .signup-link {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #555;
    }
    .signup-link a {
        color: #007bff;
        text-decoration: none;
    }
    .signup-link a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Login</h2>

    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required autocomplete="email" />

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required autocomplete="current-password" />

        <button type="submit">Log In</button>
    </form>

    <div class="signup-link">
        Don't have an account? <a href="signup.php">Sign up here</a>
    </div>
</div>
</body>
</html>
