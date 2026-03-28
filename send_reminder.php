<?php
/**
 * send_reminder.php
 * Called manually or via cron job to send daily mood reminders.
 * 
 * SETUP: Configure SMTP credentials below.
 * CRON:  0 9 * * * php /path/to/mh/send_reminder.php
 */

include 'db.php';

// ============ SMTP CONFIG — update these ============
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     'your_gmail@gmail.com');   // Your Gmail
define('SMTP_PASS',     'your_app_password');       // Gmail App Password (not your login password)
define('SMTP_FROM',     'your_gmail@gmail.com');
define('SMTP_FROM_NAME','MindBloom');
// ====================================================

function sendEmail($to, $toName, $subject, $htmlBody) {
    $boundary = md5(time());
    $headers  = implode("\r\n", [
        "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">",
        "To: $toName <$to>",
        "Subject: $subject",
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
    ]);

    // Use PHP mail() for localhost testing
    // For production, replace with PHPMailer or similar
    return mail($to, $subject, $htmlBody, $headers);
}

function sendEmailSMTP($to, $toName, $subject, $htmlBody) {
    // Simple SMTP implementation without external libraries
    $socket = fsockopen('ssl://'.SMTP_HOST, 465, $errno, $errstr, 10);
    if (!$socket) return false;

    $read = function() use ($socket) { return fgets($socket, 1024); };
    $send = function($cmd) use ($socket) { fputs($socket, $cmd."\r\n"); };

    $read(); // 220 greeting
    $send("EHLO localhost"); while(substr($read(),3,1)=='-'){}
    $send("AUTH LOGIN");     $read();
    $send(base64_encode(SMTP_USER)); $read();
    $send(base64_encode(SMTP_PASS)); $read();
    $send("MAIL FROM:<".SMTP_FROM.">"); $read();
    $send("RCPT TO:<$to>"); $read();
    $send("DATA"); $read();

    $msg  = "From: ".SMTP_FROM_NAME." <".SMTP_FROM.">\r\n";
    $msg .= "To: $toName <$to>\r\n";
    $msg .= "Subject: $subject\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $msg .= $htmlBody."\r\n";
    $send($msg."\r\n."); $read();
    $send("QUIT"); fclose($socket);
    return true;
}

// Get users who have email reminders enabled and haven't logged a mood today
$users = pg_query($conn, "
    SELECT u.id, u.username, u.email, s.email_reminder_time
    FROM users u
    JOIN settings s ON s.user_id = u.id
    WHERE s.email_reminders = TRUE
      AND u.email IS NOT NULL
      AND u.email != ''
      AND NOT EXISTS (
          SELECT 1 FROM moods m
          WHERE m.user_id = u.id
            AND m.created_at::date = CURRENT_DATE
      )
");

$sent = 0;
while ($user = pg_fetch_assoc($users)) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Inter,sans-serif;background:#f0fdf4;margin:0;padding:40px 20px;">
      <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <div style="background:linear-gradient(135deg,#22c55e,#16a34a);padding:32px;text-align:center;">
          <div style="font-size:40px;margin-bottom:8px;">🌿</div>
          <h1 style="color:#fff;font-size:22px;margin:0;font-weight:700;">MindBloom</h1>
          <p style="color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:14px;">Your Daily Wellness Reminder</p>
        </div>
        <div style="padding:32px;">
          <h2 style="color:#0f172a;font-size:20px;margin:0 0 12px;">Hi '.htmlspecialchars($user['username']).'! 👋</h2>
          <p style="color:#475569;font-size:15px;line-height:1.7;margin:0 0 20px;">
            You haven\'t logged your mood today yet. Taking just a few seconds to check in with yourself can make a big difference in your mental wellness journey.
          </p>
          <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px;margin-bottom:24px;">
            <p style="color:#15803d;font-size:14px;font-weight:600;margin:0 0 8px;">💡 Today\'s Tip</p>
            <p style="color:#166534;font-size:14px;margin:0;line-height:1.6;">
              Even logging a "neutral" mood helps you track patterns over time. Every check-in counts!
            </p>
          </div>
          <div style="text-align:center;">
            <a href="http://localhost/mh/" style="display:inline-block;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:15px;font-weight:600;">
              Log My Mood Now →
            </a>
          </div>
        </div>
        <div style="padding:20px 32px;border-top:1px solid #e2e8f0;text-align:center;">
          <p style="color:#94a3b8;font-size:12px;margin:0;">
            You\'re receiving this because you enabled daily reminders in MindBloom.<br>
            <a href="http://localhost/mh/" style="color:#22c55e;">Manage your settings</a>
          </p>
        </div>
      </div>
    </body>
    </html>';

    $success = sendEmailSMTP($user['email'], $user['username'], 'MindBloom — Time to log your mood today 🌿', $html);

    if ($success) {
        // Create in-app notification too
        pg_query_params($conn,
            "INSERT INTO notifications (user_id, type, title, message) VALUES ($1, 'reminder', $2, $3)",
            [$user['id'], 'Daily Mood Reminder', 'Don\'t forget to log your mood today! Tracking your emotions helps you understand your patterns.']
        );
        $sent++;
    }
}

echo "Sent $sent reminder(s).\n";
?>
