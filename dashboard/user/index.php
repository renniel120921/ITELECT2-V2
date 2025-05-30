<?php
session_start();
require '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user email
$stmt = $pdo->prepare("SELECT email FROM user WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$email = $user ? $user['email'] : 'N/A';

// Fetch last 5 activities for user from logs
$stmtLogs = $pdo->prepare("SELECT activity, created_at FROM logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmtLogs->execute([$user_id]);
$logs = $stmtLogs->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>User Dashboard</title>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #121212; color: #eee;
  }
  a { color: #1db954; text-decoration: none; }
  a:hover { text-decoration: underline; }
  .container {
    display: flex; min-height: 100vh;
  }
  nav.sidebar {
    width: 220px; background: #1f1f1f; padding: 20px;
    display: flex; flex-direction: column;
  }
  nav.sidebar h1 {
    font-size: 1.6rem; margin-bottom: 1rem;
    color: #1db954;
  }
  nav.sidebar a {
    padding: 10px 15px; margin: 5px 0; border-radius: 5px;
    transition: background-color 0.3s ease;
  }
  nav.sidebar a:hover {
    background-color: #1db954; color: #121212;
  }
  main.content {
    flex: 1; padding: 30px;
  }
  header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 2rem;
  }
  header h2 {
    margin: 0; font-weight: 600;
  }
  header .logout-btn {
    background: #1db954; border: none; padding: 10px 20px;
    border-radius: 25px; color: #121212; font-weight: 600;
    cursor: pointer; transition: background-color 0.3s ease;
  }
  header .logout-btn:hover {
    background: #14833b;
  }

  /* Grid for cards */
  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
  }

  /* Cards */
  .card {
    background: #222; padding: 20px; border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.6);
  }
  .card h3 {
    margin-top: 0; color: #1db954;
  }
  .card p {
    font-size: 1.1rem;
  }
  .logs-list {
    list-style: none; padding-left: 0; max-height: 150px; overflow-y: auto;
  }
  .logs-list li {
    border-bottom: 1px solid #333; padding: 6px 0;
    font-size: 0.9rem;
  }
  .logs-list li:last-child {
    border-bottom: none;
  }
  .logs-list time {
    color: #666; font-size: 0.8rem;
  }

  /* Responsive sidebar */
  @media (max-width: 600px) {
    .container { flex-direction: column; }
    nav.sidebar {
      width: 100%; flex-direction: row; justify-content: space-around;
    }
    nav.sidebar a {
      margin: 0; padding: 12px 10px; font-size: 0.9rem;
    }
  }
</style>
</head>
<body>

<div class="container">
  <nav class="sidebar">
    <h1>MyApp</h1>
    <a href="index.php">Dashboard</a>
    <a href="#">Profile</a>
    <a href="#">Settings</a>
  </nav>

  <main class="content">
    <header>
      <h2>Welcome, <?= htmlspecialchars($username) ?></h2>
      <form action="../../logout.php" method="POST" style="margin:0;">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    </header>

    <div class="grid">
      <!-- User Info -->
      <div class="card">
        <h3>User Info</h3>
        <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
      </div>

      <!-- Recent Activity Logs -->
      <div class="card">
        <h3>Recent Activity</h3>
        <?php if ($logs): ?>
        <ul class="logs-list">
          <?php foreach ($logs as $log): ?>
          <li>
            <?= htmlspecialchars($log['activity']) ?><br />
            <time><?= htmlspecialchars($log['created_at']) ?></time>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
          <p>No recent activity.</p>
        <?php endif; ?>
      </div>

      <!-- Dummy Stats -->
      <div class="card">
        <h3>Statistics</h3>
        <p><strong>Sales Today:</strong> 15</p>
        <p><strong>New Users:</strong> 3</p>
        <p><strong>Pending Tasks:</strong> 7</p>
      </div>
    </div>
  </main>
</div>

</body>
</html>
