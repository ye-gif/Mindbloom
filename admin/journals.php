<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';

$journals = pg_query($conn,
    "SELECT u.username, j.title, j.content, j.mood, j.created_at
     FROM journal j JOIN users u ON j.user_id = u.id
     ORDER BY j.created_at DESC LIMIT 100"
);
$moodEmojis = ['happy'=>'😊','calm'=>'😌','sad'=>'😢','anxious'=>'😰','angry'=>'😠','neutral'=>'😐'];
$moodColors = ['happy'=>'#f59e0b','calm'=>'#22c55e','sad'=>'#3b82f6','anxious'=>'#f97316','angry'=>'#ef4444','neutral'=>'#94a3b8'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Journals — MindBloom Admin</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡</text></svg>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-logo"><span>🛡️</span><div><div class="admin-logo-name">MindBloom</div><div class="admin-logo-sub">Admin Panel</div></div></div>
    <nav class="admin-nav">
      <a href="index.php">📊 Dashboard</a>
      <a href="users.php">👥 Users</a>
      <a href="moods.php">😊 Mood Logs</a>
      <a href="journals.php" class="active">📖 Journals</a>
    </nav>
    <div class="admin-sidebar-footer">
      <div class="admin-user">🛡️ <?= htmlspecialchars($_SESSION['admin_user']) ?></div>
      <a href="logout.php" class="admin-logout">Logout →</a>
    </div>
  </aside>
  <main class="admin-main">
    <div class="admin-topbar">
      <div><h1>Journal Entries</h1><p>All journal entries across all users</p></div>
    </div>
    <div class="admin-card">
      <table class="admin-table">
        <thead><tr><th>User</th><th>Title</th><th>Content</th><th>Mood</th><th>Date</th></tr></thead>
        <tbody>
        <?php while ($j = pg_fetch_assoc($journals)):
          $color = $j['mood'] ? ($moodColors[$j['mood']] ?? '#94a3b8') : null;
          $emoji = $j['mood'] ? ($moodEmojis[$j['mood']] ?? '') : '';
        ?>
          <tr>
            <td><?= htmlspecialchars($j['username']) ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($j['title']) ?></td>
            <td class="muted"><?= htmlspecialchars(substr($j['content'], 0, 80)) ?><?= strlen($j['content']) > 80 ? '...' : '' ?></td>
            <td><?php if ($color): ?><span class="mood-pill" style="background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>40"><?= $emoji ?> <?= ucfirst($j['mood']) ?></span><?php else: ?>—<?php endif; ?></td>
            <td class="muted"><?= date('M j, Y g:i A', strtotime($j['created_at'])) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
