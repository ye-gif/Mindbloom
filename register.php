<?php
session_start();
include 'db.php';

$error = ""; $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $hasSpecial = preg_match('/[!@#$%^&*()\-_=+\[\]{};:\'",.<>?\/\\\\|`~]/', $password);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6 || strlen($password) > 12) {
        $error = "Password must be 6–12 characters.";
    } elseif (!$hasSpecial) {
        $error = "Password must contain at least one special character.";
    } else {
        $check = pg_query_params($conn, "SELECT id FROM users WHERE username = $1 OR email = $2 LIMIT 1", [$username, $email]);
        if ($check && pg_num_rows($check) > 0) {
            $error = "Username or email already in use.";
        } else {
            $result = pg_query_params($conn, "INSERT INTO users (username, email, password) VALUES ($1, $2, $3)", [$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            if ($result) { $success = "Account created successfully!"; }
            else { $error = "Something went wrong. Please try again."; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MindBloom — Create Account</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌿</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;background:#0a0f1a;}
.left{position:relative;display:flex;flex-direction:column;justify-content:space-between;padding:48px;background:linear-gradient(145deg,#0d2818 0%,#0a3d1f 40%,#0f4d2a 100%);overflow:hidden;}
.left::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 50%,rgba(34,197,94,0.15) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(63,185,80,0.08) 0%,transparent 50%);pointer-events:none;}
.left-logo{display:flex;align-items:center;gap:12px;position:relative;z-index:1;}
.left-logo-icon{width:44px;height:44px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 4px 16px rgba(34,197,94,0.4);}
.left-logo-name{font-size:20px;font-weight:700;color:#fff;letter-spacing:-0.02em;}
.left-logo-name span{color:#4ade80;}
.left-content{position:relative;z-index:1;}
.left-content h1{font-size:38px;font-weight:700;color:#fff;line-height:1.2;letter-spacing:-0.03em;margin-bottom:16px;}
.left-content h1 span{color:#4ade80;}
.left-content p{font-size:15px;color:rgba(255,255,255,0.6);line-height:1.7;max-width:380px;}
.steps{display:flex;flex-direction:column;gap:16px;position:relative;z-index:1;}
.step{display:flex;align-items:flex-start;gap:14px;}
.step-num{width:28px;height:28px;border-radius:50%;background:rgba(63,185,80,0.2);border:1px solid rgba(63,185,80,0.4);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#4ade80;flex-shrink:0;margin-top:1px;}
.step-text strong{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,0.9);margin-bottom:2px;}
.step-text span{font-size:12px;color:rgba(255,255,255,0.5);}
.orb{position:absolute;border-radius:50%;filter:blur(60px);pointer-events:none;}
.orb1{width:300px;height:300px;background:rgba(34,197,94,0.12);top:-80px;right:-80px;}
.orb2{width:200px;height:200px;background:rgba(63,185,80,0.08);bottom:100px;left:-60px;}
.right{display:flex;align-items:center;justify-content:center;padding:48px;background:#0d1117;overflow-y:auto;}
.form-card{width:100%;max-width:420px;}
.form-header{margin-bottom:28px;}
.form-header h2{font-size:26px;font-weight:700;color:#e6edf3;letter-spacing:-0.02em;margin-bottom:6px;}
.form-header p{font-size:14px;color:#6e7681;}
.field{margin-bottom:16px;}
label{display:block;font-size:13px;font-weight:500;color:#8b949e;margin-bottom:7px;letter-spacing:0.01em;}
.input-wrap{position:relative;}
input[type=email],input[type=password],input[type=text]{width:100%;padding:13px 16px;background:#161b22;border:1.5px solid #30363d;border-radius:10px;color:#e6edf3;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:all 0.2s;}
input:focus{border-color:#3fb950;background:#1c2128;box-shadow:0 0 0 3px rgba(63,185,80,0.12);}
input::placeholder{color:#484f58;}
input.has-error{border-color:#f85149;}
.pw-eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#484f58;font-size:16px;padding:4px;transition:color 0.2s;}
.pw-eye:hover{color:#8b949e;}
.strength-wrap{margin-top:8px;}
.strength-bars{display:flex;gap:4px;margin-bottom:6px;}
.sbar{flex:1;height:3px;border-radius:99px;background:#21262d;transition:background 0.3s;}
.req-list{list-style:none;display:flex;flex-direction:column;gap:3px;}
.req-list li{font-size:12px;color:#484f58;display:flex;align-items:center;gap:6px;transition:color 0.2s;}
.req-list li::before{content:'○';font-size:10px;flex-shrink:0;}
.req-list li.met{color:#3fb950;}
.req-list li.met::before{content:'✓';}
.error-box{background:rgba(248,81,73,0.1);border:1px solid rgba(248,81,73,0.3);color:#f85149;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;}
.success-box{background:rgba(63,185,80,0.1);border:1px solid rgba(63,185,80,0.3);color:#3fb950;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;}
.success-box a{color:#3fb950;font-weight:600;}
.btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#3fb950,#2ea043);border:none;color:#fff;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:all 0.2s;box-shadow:0 2px 12px rgba(63,185,80,0.25);letter-spacing:0.01em;margin-top:4px;}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 4px 20px rgba(63,185,80,0.35);}
.btn-submit:active{transform:translateY(0);}
.form-footer{margin-top:20px;text-align:center;font-size:13px;color:#6e7681;}
.form-footer a{color:#3fb950;font-weight:500;text-decoration:none;}
.form-footer a:hover{text-decoration:underline;}
.divider{display:flex;align-items:center;gap:12px;margin:20px 0;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#21262d;}
.divider span{font-size:12px;color:#484f58;}
@media(max-width:768px){body{grid-template-columns:1fr;}.left{display:none;}.right{padding:32px 24px;min-height:100vh;}}
</style>
</head>
<body>

<div class="left">
  <div class="orb orb1"></div>
  <div class="orb orb2"></div>
  <div class="left-logo">
    <div class="left-logo-icon">🌿</div>
    <div class="left-logo-name">Mind<span>Bloom</span></div>
  </div>
  <div class="left-content">
    <h1>Begin your<br><span>wellness</span><br>journey today.</h1>
    <p>Join thousands of people who use MindBloom to understand their emotions and build healthier habits.</p>
  </div>
  <div class="steps">
    <div class="step"><div class="step-num">1</div><div class="step-text"><strong>Create your account</strong><span>Free, private, and secure</span></div></div>
    <div class="step"><div class="step-num">2</div><div class="step-text"><strong>Log your first mood</strong><span>Takes less than 10 seconds</span></div></div>
    <div class="step"><div class="step-num">3</div><div class="step-text"><strong>Discover your patterns</strong><span>Insights that help you grow</span></div></div>
  </div>
</div>

<div class="right">
  <div class="form-card">
    <div class="form-header">
      <h2>Create your account</h2>
      <p>Start your mental wellness journey for free</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="success-box">✓ <?= htmlspecialchars($success) ?> <a href="login.php">Sign in →</a></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateRegister()">
      <div class="field">
        <label>Username</label>
        <input type="text" name="username" placeholder="Choose a username" required autocomplete="username">
      </div>
      <div class="field">
        <label>Email address</label>
        <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>
      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <input type="password" name="password" id="reg-pw" placeholder="Create a strong password" required autocomplete="new-password" oninput="checkStrength(this.value)">
          <button type="button" class="pw-eye" onclick="togglePw('reg-pw',this)">👁</button>
        </div>
        <div class="strength-wrap">
          <div class="strength-bars">
            <div class="sbar" id="s1"></div><div class="sbar" id="s2"></div>
            <div class="sbar" id="s3"></div><div class="sbar" id="s4"></div>
          </div>
          <ul class="req-list">
            <li id="req-len">6–12 characters</li>
            <li id="req-special">At least one special character (!@#$%...)</li>
          </ul>
        </div>
      </div>
      <div id="reg-err" class="error-box" style="display:none;"></div>
      <button type="submit" class="btn-submit">Create Account</button>
    </form>

    <div class="divider"><span>already have an account?</span></div>
    <div class="form-footer"><a href="login.php">Sign in instead</a></div>
  </div>
</div>

<script>
function togglePw(id,btn){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}
function checkStrength(pw){
  const hasLen=pw.length>=6&&pw.length<=12;
  const hasSpecial=/[!@#$%^&*()\-_=+\[\]{};:'",.<>?\/\\|`~]/.test(pw);
  const hasUpper=/[A-Z]/.test(pw);
  const hasNum=/[0-9]/.test(pw);
  document.getElementById('req-len').className=hasLen?'met':'';
  document.getElementById('req-special').className=hasSpecial?'met':'';
  let score=0;
  if(pw.length>=6)score++;if(hasSpecial)score++;if(hasUpper)score++;if(hasNum)score++;
  const colors=['#f85149','#f97316','#eab308','#3fb950'];
  for(let i=1;i<=4;i++)document.getElementById('s'+i).style.background=i<=score?colors[score-1]:'#21262d';
}
function validateRegister(){
  const pw=document.getElementById('reg-pw').value;
  const el=document.getElementById('reg-err');
  if(pw.length<6||pw.length>12){el.style.display='block';el.textContent='⚠ Password must be 6–12 characters.';return false;}
  if(!/[!@#$%^&*()\-_=+\[\]{};:'",.<>?\/\\|`~]/.test(pw)){el.style.display='block';el.textContent='⚠ Password must contain at least one special character.';return false;}
  el.style.display='none';return true;
}
</script>
</body>
</html>
