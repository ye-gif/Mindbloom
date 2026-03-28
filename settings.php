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
  <meta name="description" content="Settings - MindBloom Mental Health Monitor" />
  <title>Settings - MindBloom</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body data-theme="light">
  <!-- Theme Toggle -->
  <div class="theme-toggle">
    <button class="theme-toggle-btn active" onclick="setTheme('light')" aria-label="Light mode">
      ☀️
    </button>
    <button class="theme-toggle-btn" onclick="setTheme('dark')" aria-label="Dark mode">
      🌙
    </button>
  </div>

  <div class="app">
    <!-- Overlay for mobile -->
    <div class="overlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="icon">♥</div>
        <span>MindBloom</span>
      </div>
      <nav>
        <a href="#" data-page="dashboard" onclick="navigateTo('dashboard');return false;">📊 Dashboard</a>
        <a href="#" data-page="mood" onclick="navigateTo('mood');return false;">😊 Log Mood</a>
        <a href="#" data-page="journal" onclick="navigateTo('journal');return false;">📖 Journal</a>
        <a href="#" data-page="trends" onclick="navigateTo('trends');return false;">📈 Mood Trends</a>
        <a href="#" data-page="chat" onclick="navigateTo('chat');return false;">💬 AI Chat</a>
        <a href="#" data-page="crisis" onclick="navigateTo('crisis');return false;">📞 Crisis Help</a>
        <a href="#" data-page="profile" onclick="navigateTo('profile');return false;">👤 Profile</a>
        <a href="#" data-page="settings" class="active" onclick="navigateTo('settings');return false;">⚙️ Settings</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">🚪 Logout</a>
      </nav>
      <div class="sidebar-footer">
        <strong>Need help now?</strong>
        Call <span class="phone">988</span> Suicide &amp; Crisis Lifeline
      </div>
    </aside>

    <!-- Main Content -->
    <div style="flex:1;display:flex;flex-direction:column;min-height:100vh;">
      <div class="mobile-header">
        <button class="hamburger" onclick="toggleSidebar()">☰</button>
        <span>MindBloom</span>
      </div>
      <div class="main">
        <div class="settings-container">

          <div class="settings-section">
            <h2><i class="ri-information-line"></i> About MindBloom</h2>
            <p class="settings-description">
              A personal mental health companion designed to help you track your moods, journal your thoughts, and develop healthier habits. All data is stored securely for complete privacy.
            </p>
            <div class="form-group">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Version</label>
                  <input type="text" class="form-input" value="1.0.0" readonly>
                </div>
                <div class="form-group">
                  <label class="form-label">Last Updated</label>
                  <input type="text" class="form-input" value="March 2024" readonly>
                </div>
              </div>
            </div>
          </div>

          <div class="settings-section">
            <h2><i class="ri-lock-line"></i> Data Privacy</h2>
            <p class="settings-description">
              Your data is stored securely and privately. We use industry-standard encryption to protect your information.
            </p>
            <div class="settings-form">
              <div class="form-switch">
                <div>
                  <label class="form-label">Secure Storage</label>
                  <p class="settings-description" style="margin-bottom: 0;">Keep all data stored securely in our database</p>
                </div>
                <label class="switch">
                  <input type="checkbox" checked disabled>
                  <span class="switch-slider"></span>
                </label>
              </div>
            </div>
          </div>

          <div class="settings-section">
            <h2><i class="ri-notification-3-line"></i> Notifications</h2>
            <p class="settings-description">
              Configure how and when you receive reminders and notifications from MindBloom.
            </p>
            <div class="settings-form">
              <div class="form-switch">
                <div>
                  <label class="form-label">Daily Mood Reminders</label>
                  <p class="settings-description" style="margin-bottom: 0;">Get reminded to log your mood daily</p>
                </div>
                <label class="switch">
                  <input type="checkbox" id="dailyReminders">
                  <span class="switch-slider"></span>
                </label>
              </div>
              <div class="form-group" id="reminderTimeGroup" style="display: none;">
                <label class="form-label">Reminder Time</label>
                <input type="time" class="form-input" id="reminderTime" value="09:00">
              </div>
              <div class="form-switch">
                <div>
                  <label class="form-label">Journal Prompts</label>
                  <p class="settings-description" style="margin-bottom: 0;">Receive writing prompts for journaling</p>
                </div>
                <label class="switch">
                  <input type="checkbox" id="journalPrompts">
                  <span class="switch-slider"></span>
                </label>
              </div>
            </div>
          </div>

          <div class="settings-section">
            <h2><i class="ri-palette-line"></i> Appearance</h2>
            <p class="settings-description">
              Customize the look and feel of your MindBloom experience.
            </p>
            <div class="settings-form">
              <div class="form-group">
                <label class="form-label">Theme Preference</label>
                <select class="form-select" id="themePreference">
                  <option value="system">Follow System</option>
                  <option value="light" selected>Light Mode</option>
                  <option value="dark">Dark Mode</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Font Size</label>
                <select class="form-select" id="fontSize">
                  <option value="small">Small</option>
                  <option value="medium" selected>Medium</option>
                  <option value="large">Large</option>
                </select>
              </div>
            </div>
          </div>

          <div class="settings-section danger-zone">
            <h2><i class="ri-error-warning-line"></i> Danger Zone</h2>
            <p class="settings-description">
              These actions cannot be undone. Please be careful with these options.
            </p>
            <div class="settings-form">
              <div class="form-group">
                <label class="form-label">Export Data</label>
                <p class="settings-description" style="margin-bottom: 16px;">
                  Download all your data as a JSON file for backup or migration.
                </p>
                <button class="btn btn-outline" onclick="exportData()">
                  <i class="ri-download-line"></i> Export Data
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="app.js"></script>
  <script>
    // Theme management
    function setTheme(theme) {
      document.body.setAttribute('data-theme', theme);
      localStorage.setItem('theme', theme);
      
      // Update button states
      document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      
      const activeBtn = theme === 'light' 
        ? document.querySelector('.theme-toggle-btn:first-child')
        : document.querySelector('.theme-toggle-btn:last-child');
      activeBtn.classList.add('active');
    }
    
    // Load saved theme or default to light
    document.addEventListener('DOMContentLoaded', function() {
      const savedTheme = localStorage.getItem('theme') || 'light';
      setTheme(savedTheme);
      
      // Check system preference
      if (!localStorage.getItem('theme')) {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        setTheme(prefersDark ? 'dark' : 'light');
      }
      
      // Listen for system theme changes
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
          setTheme(e.matches ? 'dark' : 'light');
        }
      });

      // Settings functionality
      const dailyReminders = document.getElementById('dailyReminders');
      const reminderTimeGroup = document.getElementById('reminderTimeGroup');
      
      dailyReminders.addEventListener('change', function() {
        reminderTimeGroup.style.display = this.checked ? 'block' : 'none';
      });

      // Theme preference dropdown
      document.getElementById('themePreference').addEventListener('change', function() {
        setTheme(this.value);
      });

      // Load saved settings
      loadSettings();
    });

    function loadSettings() {
      const settings = JSON.parse(localStorage.getItem('mindbloom_settings') || '{}');
      
      // Load notification settings
      if (settings.dailyReminders) {
        document.getElementById('dailyReminders').checked = true;
        document.getElementById('reminderTimeGroup').style.display = 'block';
        document.getElementById('reminderTime').value = settings.reminderTime || '09:00';
      }
      
      if (settings.journalPrompts) {
        document.getElementById('journalPrompts').checked = true;
      }
      
      // Load appearance settings
      if (settings.themePreference) {
        document.getElementById('themePreference').value = settings.themePreference;
      }
      
      if (settings.fontSize) {
        document.getElementById('fontSize').value = settings.fontSize;
        applyFontSize(settings.fontSize);
      }
    }

    function saveSettings() {
      const settings = {
        dailyReminders: document.getElementById('dailyReminders').checked,
        reminderTime: document.getElementById('reminderTime').value,
        journalPrompts: document.getElementById('journalPrompts').checked,
        themePreference: document.getElementById('themePreference').value,
        fontSize: document.getElementById('fontSize').value
      };
      
      localStorage.setItem('mindbloom_settings', JSON.stringify(settings));
      showNotification('Settings saved successfully!', 'success');
    }

    function applyFontSize(size) {
      const root = document.documentElement;
      switch(size) {
        case 'small':
          root.style.fontSize = '14px';
          break;
        case 'large':
          root.style.fontSize = '18px';
          break;
        default:
          root.style.fontSize = '16px';
      }
    }

    function exportData() {
      const data = {
        moods: JSON.parse(localStorage.getItem('mindbloom_moods') || '[]'),
        journal: JSON.parse(localStorage.getItem('mindbloom_journal') || '[]'),
        chat: JSON.parse(localStorage.getItem('mindbloom_chat') || '[]'),
        profile: JSON.parse(localStorage.getItem('mindbloom_profile') || '{}'),
        settings: JSON.parse(localStorage.getItem('mindbloom_settings') || '{}'),
        exportDate: new Date().toISOString()
      };
      
      const dataStr = JSON.stringify(data, null, 2);
      const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
      
      const exportFileDefaultName = `mindbloom-export-${new Date().toISOString().split('T')[0]}.json`;
      
      const linkElement = document.createElement('a');
      linkElement.setAttribute('href', dataUri);
      linkElement.setAttribute('download', exportFileDefaultName);
      linkElement.click();
      
      showNotification('Data exported successfully!', 'success');
    }

    function showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `settings-${type}`;
      notification.innerHTML = `
        <i class="ri-${type === 'success' ? 'check' : 'error-warning'}-line"></i>
        ${message}
      `;
      
      const container = document.querySelector('.settings-container');
      container.insertBefore(notification, container.firstChild);
      
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }

    // Auto-save settings when changed
    document.addEventListener('DOMContentLoaded', function() {
      const inputs = document.querySelectorAll('input[type="checkbox"], input[type="time"], select');
      inputs.forEach(input => {
        input.addEventListener('change', saveSettings);
      });
    });
  </script>
</body>
</html>