<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$result   = pg_query_params($conn, "SELECT * FROM settings WHERE user_id = $1", [$_SESSION['user_id']]);
$settings = $result ? pg_fetch_assoc($result) : null;

if (!$settings) {
    echo json_encode([
        'daily_reminders' => false,
        'reminder_time'   => '09:00:00',
        'journal_prompts' => false,
        'theme'           => 'light',
        'font_size'       => 'medium'
    ]);
} else {
    echo json_encode($settings);
}
?>
