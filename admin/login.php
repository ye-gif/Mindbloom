<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simple hardcoded admin credentials — change these!
    $ADMIN_USER = 'admin';
    $ADMIN_PASS = 'admin@102005';

    if ($_POST['username'] === $ADMIN_USER && $_POST['password'] === $ADMIN_PASS) {
        $_SESSION['admin_id']   = 1;
        $_SESSION['admin_user'] = $ADMIN_USER;
        header('Location: index.php'); exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MindBloom Admin</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡</text></svg>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
    }
    .login-box {
      background: #1e293b;
      border: 1px solid #334155;
      border-radius: 16px;
      padding: 40px;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }
    .logo { text-align: center; margin-bottom: 28px; }
    .logo-icon { font-size: 40px; display: block; margin-bottom: 8px; }
    .logo h1 { font-size: 20px; color: #f1f5f9; font-weight: 700; }
    .logo p  { font-size: 13px; color: #64748b; margin-top: 4px; }
    label { display: block; font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; }
    input {
      width: 100%; padding: 11px 14px;
      background: #0f172a; border: 1.5px solid #334155;
      border-radius: 8px; color: #f1f5f9; font-size: 14px;
      outline: none; transition: border-color 0.2s;
      font-family: 'Inter', sans-serif;
    }
    input:focus { border-color: #22c55e; }
    .field { margin-bottom: 16px; }
    .btn {
      width: 100%; padding: 12px;
      background: linear-gradient(135deg, #22c55e, #16a34a);
      border: none; border-radius: 8px;
      color: white; font-size: 15px; font-weight: 600;
      cursor: pointer; margin-top: 8px;
      font-family: 'Inter', sans-serif;
      transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.9; }
    .error {
      background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
      color: #fca5a5; padding: 10px 14px; border-radius: 8px;
      font-size: 13px; margin-bottom: 16px; text-align: center;
    }
  </style>
</head>
<body>
<div class="login-box">
  <div class="logo">
    <span class="logo-icon">🛡️</span>
    <h1>MindBloom Admin</h1>
    <p>Restricted access — authorized personnel only</p>
  </div>
  <?php if ($error): ?>
    <div class="error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="field">
      <label>Username</label>
      <input type="text" name="username" required autofocus placeholder="admin">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required placeholder="••••••••">
    </div>
    <button type="submit" class="btn">Sign In</button>
  </form>
</div>
</body>
</html>
