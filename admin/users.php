<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $uid = (int)$_POST['user_id'];
    pg_query_params($conn, "DELETE FROM users WHERE id = $1", [$uid]);
    header('Location: users.php?msg=deleted'); exit;
}

$search = trim($_GET['q'] ?? '');
if ($search) {
    $users = pg_query_params($conn,
        "SELECT u.id, u.username, u.email, u.avatar, u.created_at, u.last_login,
                COUNT(DISTINCT m.id) AS mood_count,
                COUNT(DISTINCT j.id) AS journal_count
         FROM users u
         LEFT JOIN moods m ON m.user_id = u.id
         LEFT JOIN journal j ON j.user_id = u.id
         WHERE u.username ILIKE $1 OR u.email ILIKE $1
         GROUP BY u.id ORDER BY u.created_at DESC",
        ['%' . $search . '%']
    );
} else {
    $users = pg_query($conn,
        "SELECT u.id, u.username, u.email, u.avatar, u.created_at, u.last_login,
                COUNT(DISTINCT m.id) AS mood_count,
                COUNT(DISTINCT j.id) AS journal_count
         FROM users u
         LEFT JOIN moods m ON m.user_id = u.id
         LEFT JOIN journal j ON j.user_id = u.id
         GROUP BY u.id ORDER BY u.created_at DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users — MindBloom Admin</title>
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
      <a href="users.php" class="active">👥 Users</a>
      <a href="moods.php">😊 Mood Logs</a>
      <a href="journals.php">📖 Journals</a>
    </nav>
    <div class="admin-sidebar-footer">
      <div class="admin-user">🛡️ <?= htmlspecialchars($_SESSION['admin_user']) ?></div>
      <a href="logout.php" class="admin-logout">Logout →</a>
    </div>
  </aside>
  <main class="admin-main">
    <div class="admin-topbar">
      <div><h1>Users</h1><p>Manage all registered users</p></div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
      <div class="alert-success">✓ User deleted successfully</div>
    <?php endif; ?>

    <div class="admin-card">
      <div class="admin-card-header">
        <h2>All Users (<?= pg_num_rows($users) ?>)</h2>
        <form method="GET" style="display:flex;gap:8px;">
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search username or email..." class="search-input">
          <button type="submit" class="btn-search">Search</button>
          <?php if ($search): ?><a href="users.php" class="btn-clear">✕</a><?php endif; ?>
        </form>
      </div>
      <table class="admin-table">
        <thead><tr><th>ID</th><th>User</th><th>Email</th><th>Moods</th><th>Journals</th><th>Joined</th><th>Last Login</th><th>Action</th></tr></thead>
        <tbody>
        <?php while ($u = pg_fetch_assoc($users)): ?>
          <tr>
            <td class="muted">#<?= $u['id'] ?></td>
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
            <td><span class="badge blue"><?= $u['mood_count'] ?></span></td>
            <td><span class="badge purple"><?= $u['journal_count'] ?></span></td>
            <td class="muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td class="muted"><?= $u['last_login'] ? date('M j, Y', strtotime($u['last_login'])) : '—' ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Delete user <?= htmlspecialchars($u['username']) ?> and all their data?')">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button type="submit" name="delete_user" class="btn-delete">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
