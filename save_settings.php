<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$sql    = "INSERT INTO settings (user_id, daily_reminders, reminder_time, journal_prompts, theme, font_size)
           VALUES ($1, $2, $3, $4, $5, $6)
           ON CONFLICT (user_id) DO UPDATE SET
               daily_reminders = EXCLUDED.daily_reminders,
               reminder_time   = EXCLUDED.reminder_time,
               journal_prompts = EXCLUDED.journal_prompts,
               theme           = EXCLUDED.theme,
               font_size       = EXCLUDED.font_size,
               updated_at      = NOW()";
$result = pg_query_params($conn, $sql, [
    $_SESSION['user_id'],
    ($input['dailyReminders'] ?? false) ? 'true' : 'false',
    $input['reminderTime'] ?? '09:00:00',
    ($input['journalPrompts'] ?? false) ? 'true' : 'false',
    $input['theme']     ?? 'light',
    $input['fontSize']  ?? 'medium'
]);

echo $result
    ? json_encode(['success' => true])
    : json_encode(['success' => false, 'error' => pg_last_error($conn)]);
?>
