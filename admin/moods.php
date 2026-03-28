<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';

$filter = $_GET['mood'] ?? '';
$moodEmojis = ['happy'=>'😊','calm'=>'😌','sad'=>'😢','anxious'=>'😰','angry'=>'😠','neutral'=>'😐'];
$moodColors = ['happy'=>'#f59e0b','calm'=>'#22c55e','sad'=>'#3b82f6','anxious'=>'#f97316','angry'=>'#ef4444','neutral'=>'#94a3b8'];

if ($filter) {
    $moods = pg_query_params($conn,
        "SELECT u.username, m.mood, m.note, m.intensity, m.created_at
         FROM moods m JOIN users u ON m.user_id = u.id
         WHERE m.mood = $1 ORDER BY m.created_at DESC LIMIT 100",
        [$filter]
    );
} else {
    $moods = pg_query($conn,
        "SELECT u.username, m.mood, m.note, m.intensity, m.created_at
         FROM moods m JOIN users u ON m.user_id = u.id
         ORDER BY m.created_at DESC LIMIT 100"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mood Logs — MindBloom Admin</title>
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
      <a href="moods.php" class="active">😊 Mood Logs</a>
      <a href="journals.php">📖 Journals</a>
    </nav>
    <div class="admin-sidebar-footer">
      <div class="admin-user">🛡️ <?= htmlspecialchars($_SESSION['admin_user']) ?></div>
      <a href="logout.php" class="admin-logout">Logout →</a>
    </div>
  </aside>
  <main class="admin-main">
    <div class="admin-topbar">
      <div><h1>Mood Logs</h1><p>All mood entries across all users</p></div>
    </div>
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Filter by Mood</h2>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
        <a href="moods.php" class="mood-filter-btn <?= !$filter ? 'active' : '' ?>">All</a>
        <?php foreach ($moodEmojis as $mood => $emoji): ?>
          <a href="moods.php?mood=<?= $mood ?>" class="mood-filter-btn <?= $filter === $mood ? 'active' : '' ?>"
             style="<?= $filter === $mood ? 'background:' . $moodColors[$mood] . '20;border-color:' . $moodColors[$mood] . ';color:' . $moodColors[$mood] : '' ?>">
            <?= $emoji ?> <?= ucfirst($mood) ?>
          </a>
        <?php endforeach; ?>
      </div>
      <table class="admin-table">
        <thead><tr><th>User</th><th>Mood</th><th>Note</th><th>Intensity</th><th>Time</th></tr></thead>
        <tbody>
        <?php while ($m = pg_fetch_assoc($moods)):
          $color = $moodColors[$m['mood']] ?? '#94a3b8';
          $emoji = $moodEmojis[$m['mood']] ?? '😐';
        ?>
          <tr>
            <td><?= htmlspecialchars($m['username']) ?></td>
            <td><span class="mood-pill" style="background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>40"><?= $emoji ?> <?= ucfirst($m['mood']) ?></span></td>
            <td class="muted"><?= $m['note'] ? htmlspecialchars(substr($m['note'], 0, 60)) . (strlen($m['note']) > 60 ? '...' : '') : '—' ?></td>
            <td><?= $m['intensity'] ?>/10</td>
            <td class="muted"><?= date('M j, Y g:i A', strtotime($m['created_at'])) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
