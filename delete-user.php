<?php
$message = "";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=itelect2;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ Invalid email format.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $userId = $user['id'];
            try {
                $pdo->beginTransaction();

                $pdo->prepare("DELETE FROM logs WHERE user_id = ?")->execute([$userId]);
                $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$userId]);

                $pdo->commit();
                $message = "✅ User with email '".htmlspecialchars($email)."' and related logs have been deleted.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "❌ Failed to delete user: " . htmlspecialchars($e->getMessage());
            }
        } else {
            $message = "⚠️ No user found with email '".htmlspecialchars($email)."'.";
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
    body { font-family: Arial, sans-serif; padding: 20px; background: #121212; color: #eee; }
    input, button { padding: 10px; margin: 10px 0; width: 320px; border-radius: 5px; border: none; font-size: 16px; }
    button { background: #ff4444; color: #fff; cursor: pointer; font-weight: bold; transition: background 0.3s ease; }
    button:hover { background: #cc0000; }
    .message { margin: 20px 0; font-weight: 700; }
</style>
</head>
<body>
    <h2>Delete User by Email <small style="color:#f55;">(For Testing Only!)</small></h2>
    <form method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Enter email to delete" required />
        <br />
        <button type="submit">Delete User</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
</body>
</html>
