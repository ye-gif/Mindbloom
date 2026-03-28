<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $result   = pg_query_params($conn, "SELECT * FROM users WHERE email = $1 LIMIT 1", [$email]);
    if ($result) {
        $user = pg_fetch_assoc($result);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            pg_query_params($conn, "UPDATE users SET last_login = NOW() WHERE id = $1", [$user['id']]);
            header("Location: index.php"); exit;
        } else { $error = "Invalid email or password."; }
    } else { $error = "Something went wrong. Please try again."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MindBloom — Sign In</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌿</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;background:#0a0f1a;}

/* Left panel */
.left{position:relative;display:flex;flex-direction:column;justify-content:space-between;padding:48px;background:linear-gradient(145deg,#0d2818 0%,#0a3d1f 40%,#0f4d2a 100%);overflow:hidden;}
.left::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 50%,rgba(34,197,94,0.15) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(63,185,80,0.08) 0%,transparent 50%);pointer-events:none;}
.left-logo{display:flex;align-items:center;gap:12px;position:relative;z-index:1;}
.left-logo-icon{width:44px;height:44px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 4px 16px rgba(34,197,94,0.4);}
.left-logo-name{font-size:20px;font-weight:700;color:#fff;letter-spacing:-0.02em;}
.left-logo-name span{color:#4ade80;}
.left-content{position:relative;z-index:1;}
.left-content h1{font-size:42px;font-weight:700;color:#fff;line-height:1.2;letter-spacing:-0.03em;margin-bottom:16px;}
.left-content h1 span{color:#4ade80;}
.left-content p{font-size:16px;color:rgba(255,255,255,0.6);line-height:1.7;max-width:380px;}
.left-features{display:flex;flex-direction:column;gap:12px;position:relative;z-index:1;}
.feature{display:flex;align-items:center;gap:12px;padding:14px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:12px;backdrop-filter:blur(10px);}
.feature-icon{font-size:20px;flex-shrink:0;}
.feature-text{font-size:13px;color:rgba(255,255,255,0.75);font-weight:500;}

/* Floating orbs */
.orb{position:absolute;border-radius:50%;filter:blur(60px);pointer-events:none;}
.orb1{width:300px;height:300px;background:rgba(34,197,94,0.12);top:-80px;right:-80px;}
.orb2{width:200px;height:200px;background:rgba(63,185,80,0.08);bottom:100px;left:-60px;}

/* Right panel */
.right{display:flex;align-items:center;justify-content:center;padding:48px;background:#0d1117;}
.form-card{width:100%;max-width:420px;}
.form-header{margin-bottom:36px;}
.form-header h2{font-size:28px;font-weight:700;color:#e6edf3;letter-spacing:-0.02em;margin-bottom:6px;}
.form-header p{font-size:14px;color:#6e7681;}
.field{margin-bottom:18px;}
label{display:block;font-size:13px;font-weight:500;color:#8b949e;margin-bottom:8px;letter-spacing:0.01em;}
.input-wrap{position:relative;}
input[type=email],input[type=password],input[type=text]{width:100%;padding:13px 16px;background:#161b22;border:1.5px solid #30363d;border-radius:10px;color:#e6edf3;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:all 0.2s;}
input:focus{border-color:#3fb950;background:#1c2128;box-shadow:0 0 0 3px rgba(63,185,80,0.12);}
input::placeholder{color:#484f58;}
.pw-eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#484f58;font-size:16px;padding:4px;transition:color 0.2s;}
.pw-eye:hover{color:#8b949e;}
.error-box{background:rgba(248,81,73,0.1);border:1px solid rgba(248,81,73,0.3);color:#f85149;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;}
.btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#3fb950,#2ea043);border:none;color:#fff;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:all 0.2s;box-shadow:0 2px 12px rgba(63,185,80,0.25);letter-spacing:0.01em;}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 4px 20px rgba(63,185,80,0.35);}
.btn-submit:active{transform:translateY(0);}
.form-footer{margin-top:24px;text-align:center;font-size:13px;color:#6e7681;}
.form-footer a{color:#3fb950;font-weight:500;text-decoration:none;}
.form-footer a:hover{text-decoration:underline;}
.divider{display:flex;align-items:center;gap:12px;margin:24px 0;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#21262d;}
.divider span{font-size:12px;color:#484f58;}

@media(max-width:768px){
  body{grid-template-columns:1fr;}
  .left{display:none;}
  .right{padding:32px 24px;background:#0d1117;min-height:100vh;}
}
</style>
</head>
<body>

<!-- Left Panel -->
<div class="left">
  <div class="orb orb1"></div>
  <div class="orb orb2"></div>

  <div class="left-logo">
    <div class="left-logo-icon">🌿</div>
    <div class="left-logo-name">Mind<span>Bloom</span></div>
  </div>

  <div class="left-content">
    <h1>Your mental<br>wellness <span>journey</span><br>starts here.</h1>
    <p>Track your moods, journal your thoughts, and build healthier habits — all in one safe space.</p>
  </div>

  <div class="left-features">
    <div class="feature"><span class="feature-icon">😊</span><span class="feature-text">Daily mood tracking with color psychology</span></div>
    <div class="feature"><span class="feature-icon">📖</span><span class="feature-text">Private journal with mood tagging</span></div>
    <div class="feature"><span class="feature-icon">🤖</span><span class="feature-text">AI wellness companion available 24/7</span></div>
  </div>
</div>

<!-- Right Panel -->
<div class="right">
  <div class="form-card">
    <div class="form-header">
      <h2>Welcome back</h2>
      <p>Sign in to continue your wellness journey</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateLogin()">
      <div class="field">
        <label>Email address</label>
        <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>
      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <input type="password" name="password" id="login-pw" placeholder="Your password" required autocomplete="current-password">
          <button type="button" class="pw-eye" onclick="togglePw('login-pw',this)">👁</button>
        </div>
      </div>
      <div id="login-err" class="error-box" style="display:none;"></div>
      <button type="submit" class="btn-submit">Sign In</button>
    </form>

    <div class="divider"><span>or</span></div>
    <div class="form-footer">Don't have an account? <a href="register.php">Create one free</a></div>
  </div>
</div>

<script>
function togglePw(id,btn){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}
function validateLogin(){
  const pw=document.getElementById('login-pw').value;
  const el=document.getElementById('login-err');
  if(pw.length<6||pw.length>12){el.style.display='block';el.textContent='⚠ Password must be 6–12 characters.';return false;}
  if(!/[!@#$%^&*()\-_=+\[\]{};:'",.<>?\/\\|`~]/.test(pw)){el.style.display='block';el.textContent='⚠ Password must contain at least one special character.';return false;}
  el.style.display='none';return true;
}
</script>
</body>
</html>
