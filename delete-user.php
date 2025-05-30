<?php
$conn = new mysqli('localhost', 'root', '', 'itelect2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);

    $result = $conn->query("SELECT * FROM user WHERE email='$email'");

    if ($result->num_rows > 0) {
        $conn->query("DELETE FROM user WHERE email='$email'");
        $message = "User with email '$email' has been deleted.";
    } else {
        $message = "No user found with email '$email'.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Delete User</title>
<style>
    body { font-family: Arial; padding: 20px; background: #121212; color: #eee; }
    input, button { padding: 10px; margin: 10px 0; width: 300px; border-radius: 5px; border: none; }
    button { background: #ff4444; color: white; cursor: pointer; font-weight: bold; }
    .message { margin: 20px 0; font-weight: 700; }
</style>
</head>
<body>
    <h2>Delete User by Email (For Testing Only!)</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter email to delete" required />
        <br />
        <button type="submit">Delete User</button>
    </form>
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
</body>
</html>
