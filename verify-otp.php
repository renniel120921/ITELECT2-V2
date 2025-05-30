<?php
require_once 'dashboard/admin/authentication/admin-class.php';
$admin = new Admin();
$email = $_GET['email'] ?? '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = $_POST['otp'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($admin->verifyOtp($email, $enteredOtp)) {
        // Success - redirect to login with message (or alert)
        header("Location: login.php?verified=1");
        exit();
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Verify OTP</title>
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
        text-align: center;
    }
    h2 {
        margin-bottom: 1.5rem;
        color: #333;
    }
    label {
        display: block;
        margin-bottom: 0.4rem;
        color: #555;
        text-align: left;
    }
    input[type="text"] {
        width: 100%;
        padding: 0.6rem;
        margin-bottom: 1.2rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1rem;
        box-sizing: border-box;
    }
    button {
        width: 100%;
        padding: 0.7rem;
        background-color: #28a745;
        border: none;
        color: white;
        font-size: 1.1rem;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #218838;
    }
    .error {
        background: #f8d7da;
        color: #842029;
        padding: 0.7rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        border: 1px solid #f5c2c7;
        font-size: 0.9rem;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Verify OTP</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <label for="otp">Enter OTP:</label>
        <input type="text" id="otp" name="otp" required maxlength="6" pattern="\d{6}" title="Please enter a 6-digit OTP">

        <button type="submit">Verify</button>
    </form>
</div>
</body>
</html>
