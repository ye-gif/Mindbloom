<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MindBloom — Mental Health Monitor</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌿</text></svg>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body data-theme="light">

  <!-- Notification Bell -->
  <div class="notif-wrap" id="notif-wrap">
    <button class="notif-bell" onclick="window._toggleNotifications()" aria-label="Notifications" title="Notifications">
      🔔<span class="notif-badge" id="notif-badge" style="display:none;"></span>
    </button>
    <div class="notif-dropdown" id="notif-dropdown" style="display:none;">
      <div class="notif-header">
        <span>Notifications</span>
        <button onclick="window._markAllRead()">Mark all read</button>
      </div>
      <div class="notif-list" id="notif-list">
        <div class="notif-empty">No notifications yet</div>
      </div>
    </div>
  </div>

  <!-- Custom Confirm Modal -->
  <div id="confirm-modal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;">
    <div id="confirm-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(6px);"></div>
    <div id="confirm-box" style="position:relative;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:28px 28px 24px;max-width:360px;width:90%;box-shadow:0 24px 48px rgba(0,0,0,0.2);animation:pageIn 0.2s ease;">
      <div id="confirm-icon" style="font-size:36px;text-align:center;margin-bottom:12px;"></div>
      <h3 id="confirm-title" style="font-size:17px;font-weight:700;color:var(--fg);text-align:center;margin-bottom:8px;"></h3>
      <p id="confirm-msg" style="font-size:14px;color:var(--fg-muted);text-align:center;line-height:1.6;margin-bottom:24px;"></p>
      <div style="display:flex;gap:10px;">
        <button id="confirm-cancel" style="flex:1;padding:11px;border-radius:10px;border:1.5px solid var(--border);background:transparent;color:var(--fg);font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font-sans);transition:all 0.2s;">Cancel</button>
        <button id="confirm-ok" style="flex:1;padding:11px;border-radius:10px;border:none;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font-sans);transition:all 0.2s;"></button>
      </div>
    </div>
  </div>

  <div class="app">
    <div class="overlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="sidebar-logo-icon">🌿</div>
        <div class="sidebar-logo-text">
          <span class="sidebar-logo-name">Mind<span style="color:var(--accent)">Bloom</span></span>
          <span class="sidebar-logo-tagline">Wellness Journal</span>
        </div>
      </div>

      <div class="sidebar-user" onclick="navigateTo('profile')" style="cursor:pointer;" title="View Profile">
        <div class="sidebar-user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?></div>
        <div class="sidebar-user-info">
          <span class="sidebar-user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Friend') ?></span>
          <span class="sidebar-user-status">● Active</span>
        </div>
      </div>

      <nav>
        <div class="nav-section-label">Main</div>
        <a href="#" data-page="dashboard" class="active" onclick="navigateTo('dashboard');return false;">
          <span class="nav-icon">⊞</span><span class="nav-label">Dashboard</span>
        </a>
        <a href="#" data-page="mood" onclick="navigateTo('mood');return false;">
          <span class="nav-icon">◎</span><span class="nav-label">Log Mood</span>
        </a>
        <a href="#" data-page="journal" onclick="navigateTo('journal');return false;">
          <span class="nav-icon">✎</span><span class="nav-label">Journal</span>
        </a>
        <a href="#" data-page="trends" onclick="navigateTo('trends');return false;">
          <span class="nav-icon">↗</span><span class="nav-label">Mood Trends</span>
        </a>

        <div class="nav-section-label">Support</div>
        <a href="#" data-page="chat" onclick="navigateTo('chat');return false;">
          <span class="nav-icon">◉</span><span class="nav-label">AI Chat</span>
        </a>
        <a href="#" data-page="crisis" onclick="navigateTo('crisis');return false;">
          <span class="nav-icon">♡</span><span class="nav-label">Crisis Help</span>
        </a>

        <div class="nav-section-label">Account</div>
        <a href="#" data-page="profile" onclick="navigateTo('profile');return false;">
          <span class="nav-icon">◷</span><span class="nav-label">Profile</span>
        </a>
        <a href="#" data-page="settings" onclick="navigateTo('settings');return false;">
          <span class="nav-icon">⊙</span><span class="nav-label">Settings</span>
        </a>
        <a href="#" class="nav-logout" onclick="window._confirmLogout();return false;">
          <span class="nav-icon">→</span><span class="nav-label">Logout</span>
        </a>
      </nav>

      <div class="sidebar-crisis">
        <div class="sidebar-crisis-icon">🆘</div>
        <div>
          <div class="sidebar-crisis-title">Need help now?</div>
          <div class="sidebar-crisis-number">Call 988</div>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
      <div class="mobile-header">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
        <div class="mobile-logo">🌿 MindBloom</div>
        <button class="notif-bell" id="notif-bell-mobile" onclick="window._toggleNotifications()" aria-label="Notifications">
          🔔<span class="notif-badge" id="notif-badge-mobile" style="display:none;"></span>
        </button>
      </div>
      <main class="main">
        <div id="page-dashboard" class="page active"></div>
        <div id="page-mood"      class="page"></div>
        <div id="page-journal"   class="page"></div>
        <div id="page-trends"    class="page"></div>
        <div id="page-chat"      class="page"></div>
        <div id="page-crisis"    class="page"></div>
        <div id="page-profile"   class="page"></div>
        <div id="page-settings"  class="page"></div>
      </main>
    </div>
  </div>

  <script src="app_data.js"></script>
  <script src="app_core.js"></script>
  <script src="app_dashboard.js"></script>
  <script src="app_pages.js"></script>
  <script src="app_trends_chat.js"></script>
  <script src="app_profile_settings.js"></script>
  <script>
    USERNAME = <?= json_encode($_SESSION['username'] ?? 'Friend') ?>;

    function setTheme(theme) {
      document.body.setAttribute('data-theme', theme);
      localStorage.setItem('theme', theme);
      document.querySelectorAll('.theme-toggle-btn').forEach(b => b.classList.remove('active'));
      const btn = theme === 'light'
        ? document.querySelector('.theme-toggle-btn:first-child')
        : document.querySelector('.theme-toggle-btn:last-child');
      if (btn) btn.classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
      const saved = localStorage.getItem('theme');
      if (saved) { setTheme(saved); }
      else {
        setTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      }
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (!localStorage.getItem('theme')) setTheme(e.matches ? 'dark' : 'light');
      });
    });
  </script>
</body>
</html>
