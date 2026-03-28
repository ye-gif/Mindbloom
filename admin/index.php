<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';

// ---- Stats ----
$totalUsers   = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS c FROM users"))['c'];
$totalMoods   = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS c FROM moods"))['c'];
$totalJournal = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS c FROM journal"))['c'];
$todayUsers   = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS c FROM users WHERE created_at::date = CURRENT_DATE"))['c'];
$todayMoods   = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS c FROM moods WHERE created_at::date = CURRENT_DATE"))['c'];

// ---- Mood distribution ----
$moodDist = pg_query($conn, "SELECT mood, COUNT(*) AS cnt FROM moods GROUP BY mood ORDER BY cnt DESC");

// ---- Last 7 days mood activity ----
$weekActivity = pg_query($conn, "
    SELECT DATE(created_at) AS day, COUNT(*) AS cnt
    FROM moods
    WHERE created_at >= NOW() - INTERVAL '7 days'
    GROUP BY DATE(created_at)
    ORDER BY day ASC
");
$weekData = [];
while ($r = pg_fetch_assoc($weekActivity)) $weekData[$r['day']] = $r['cnt'];

// ---- Recent users ----
$recentUsers = pg_query($conn, "SELECT id, username, email, avatar, created_at FROM users ORDER BY created_at DESC LIMIT 10");

// ---- Recent moods ----
$recentMoods = pg_query($conn, "
    SELECT u.username, m.mood, m.note, m.created_at
    FROM moods m JOIN users u ON m.user_id = u.id
    ORDER BY m.created_at DESC LIMIT 10
");

$moodColors = [
    'happy'   => '#f59e0b',
    'calm'    => '#22c55e',
    'sad'     => '#3b82f6',
    'anxious' => '#f97316',
    'angry'   => '#ef4444',
    'neutral' => '#94a3b8',
];
$moodEmojis = [
    'happy'=>'😊','calm'=>'😌','sad'=>'😢','anxious'=>'😰','angry'=>'😠','neutral'=>'😐'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MindBloom Admin Dashboard</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡</text></svg>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-logo">
      <span>🛡️</span>
      <div>
        <div class="admin-logo-name">MindBloom</div>
        <div class="admin-logo-sub">Admin Panel</div>
      </div>
    </div>
    <nav class="admin-nav">
      <a href="index.php" class="active">📊 Dashboard</a>
      <a href="users.php">👥 Users</a>
      <a href="moods.php">😊 Mood Logs</a>
      <a href="journals.php">📖 Journals</a>
    </nav>
    <div class="admin-sidebar-footer">
      <div class="admin-user">🛡️ <?= htmlspecialchars($_SESSION['admin_user']) ?></div>
      <a href="logout.php" class="admin-logout">Logout →</a>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">

    <div class="admin-topbar">
      <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_user']) ?>. Here's what's happening.</p>
      </div>
      <div class="admin-date"><?= date('l, F j, Y') ?></div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,0.1);color:#22c55e;">👥</div>
        <div class="stat-info">
          <div class="stat-value"><?= $totalUsers ?></div>
          <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-badge green">+<?= $todayUsers ?> today</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6;">😊</div>
        <div class="stat-info">
          <div class="stat-value"><?= $totalMoods ?></div>
          <div class="stat-label">Mood Entries</div>
        </div>
        <div class="stat-badge blue">+<?= $todayMoods ?> today</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168,85,247,0.1);color:#a855f7;">📖</div>
        <div class="stat-info">
          <div class="stat-value"><?= $totalJournal ?></div>
          <div class="stat-label">Journal Entries</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">📈</div>
        <div class="stat-info">
          <div class="stat-value"><?= $totalUsers > 0 ? round($totalMoods / $totalUsers, 1) : 0 ?></div>
          <div class="stat-label">Avg Moods/User</div>
        </div>
      </div>
    </div>

    <!-- Charts row -->
    <div class="admin-grid-2">

      <!-- Weekly Activity -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Weekly Mood Activity</h2>
          <span class="badge">Last 7 days</span>
        </div>
        <div class="week-chart">
          <?php
          for ($i = 6; $i >= 0; $i--) {
              $date  = date('Y-m-d', strtotime("-$i days"));
              $day   = date('D', strtotime($date));
              $count = $weekData[$date] ?? 0;
              $max   = max(array_values($weekData) ?: [1]);
              $pct   = $max > 0 ? round(($count / $max) * 100) : 0;
              $isToday = $date === date('Y-m-d');
          ?>
          <div class="week-col">
            <div class="week-val"><?= $count ?: '' ?></div>
            <div class="week-bar-wrap">
              <div class="week-bar <?= $isToday ? 'today' : '' ?>" style="height:<?= max($pct, $count > 0 ? 8 : 2) ?>%"></div>
            </div>
            <div class="week-label <?= $isToday ? 'today' : '' ?>"><?= $day ?></div>
          </div>
          <?php } ?>
        </div>
      </div>

      <!-- Mood Distribution -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Mood Distribution</h2>
          <span class="badge"><?= $totalMoods ?> total</span>
        </div>
        <div class="mood-dist">
          <?php while ($row = pg_fetch_assoc($moodDist)):
            $pct   = $totalMoods > 0 ? round(($row['cnt'] / $totalMoods) * 100) : 0;
            $color = $moodColors[$row['mood']] ?? '#94a3b8';
            $emoji = $moodEmojis[$row['mood']] ?? '😐';
          ?>
          <div class="mood-dist-row">
            <div class="mood-dist-label"><?= $emoji ?> <?= ucfirst($row['mood']) ?></div>
            <div class="mood-dist-bar-wrap">
              <div class="mood-dist-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
            </div>
            <div class="mood-dist-count"><?= $row['cnt'] ?> <span>(<?= $pct ?>%)</span></div>
          </div>
          <?php endwhile; ?>
        </div>
      </div>

    </div>

    <!-- Tables row -->
    <div class="admin-grid-2">

      <!-- Recent Users -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Recent Users</h2>
          <a href="users.php">View all →</a>
        </div>
        <table class="admin-table">
          <thead><tr><th>User</th><th>Email</th><th>Joined</th></tr></thead>
          <tbody>
          <?php while ($u = pg_fetch_assoc($recentUsers)): ?>
            <tr>
              <td><div class="user-cell">
                <?php if (!empty($u['avatar'])): ?>
                  <img src="../<?= htmlspecialchars($u['avatar']) ?>"
                       onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                       style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid #30363d;">
                  <div class="user-avatar" style="display:none;"><?= strtoupper(substr($u['username'],0,1)) ?></div>
                <?php else: ?>
                  <div class="user-avatar"><?= strtoupper(substr($u['username'],0,1)) ?></div>
                <?php endif; ?>
                <?= htmlspecialchars($u['username']) ?>
              </div></td>
              <td class="muted"><?= htmlspecialchars($u['email']) ?></td>
              <td class="muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <!-- Recent Moods -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Recent Mood Logs</h2>
          <a href="moods.php">View all →</a>
        </div>
        <table class="admin-table">
          <thead><tr><th>User</th><th>Mood</th><th>Time</th></tr></thead>
          <tbody>
          <?php while ($m = pg_fetch_assoc($recentMoods)):
            $color = $moodColors[$m['mood']] ?? '#94a3b8';
            $emoji = $moodEmojis[$m['mood']] ?? '😐';
          ?>
            <tr>
              <td><?= htmlspecialchars($m['username']) ?></td>
              <td><span class="mood-pill" style="background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>40"><?= $emoji ?> <?= ucfirst($m['mood']) ?></span></td>
              <td class="muted"><?= date('M j, g:i A', strtotime($m['created_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>

  </main>
</div>

</body>
</html>
