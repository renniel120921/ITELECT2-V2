<?php
    include_once 'register.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="src/css/login.css">
</head>
<body>
    <div class="form-container">
        <div class="form-box">
            <h1>REGISTRATION</h1>
            <form action="dashboard/admin/authentication/admin-class.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="text" name="username" placeholder="Enter Username" required>
                <input type="email" name="email" placeholder="Enter Email" required>
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit" name="btn-signup">SIGN UP</button>
                <p>Already have an account? <a href="Index.php">Sign in here</a></p>
            </form>
        </div>
    </div>
</body>
</html>
