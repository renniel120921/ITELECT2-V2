<?php
$message = "";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=itelect2;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Find user by email
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $userId = $user['id'];

            try {
                // Begin transaction
                $pdo->beginTransaction();

                // Delete logs related to user
                $stmtDeleteLogs = $pdo->prepare("DELETE FROM logs WHERE user_id = ?");
                $stmtDeleteLogs->execute([$userId]);

                // Delete user
                $stmtDeleteUser = $pdo->prepare("DELETE FROM user WHERE id = ?");
                $stmtDeleteUser->execute([$userId]);

                // Commit transaction
                $pdo->commit();

                $message = "User with email '$email' and related logs have been deleted.";
            } catch (Exception $e) {
                // Rollback on error
                $pdo->rollBack();
                $message = "Failed to delete user: " . $e->getMessage();
            }
        } else {
            $message = "No user found with email '$email'.";
        }
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
    <form method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Enter email to delete" required />
        <br />
        <button type="submit">Delete User</button>
    </form>
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
</body>
</html>
