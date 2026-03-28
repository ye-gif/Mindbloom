<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Unauthorized']); exit; }

$result = pg_query_params($conn,
    "SELECT id, type, title, message, is_read, created_at
     FROM notifications WHERE user_id = $1
     ORDER BY created_at DESC LIMIT 20",
    [$_SESSION['user_id']]
);

$notifications = [];
while ($row = pg_fetch_assoc($result)) {
    $notifications[] = $row;
}

$unread = pg_fetch_assoc(pg_query_params($conn,
    "SELECT COUNT(*) AS c FROM notifications WHERE user_id = $1 AND is_read = FALSE",
    [$_SESSION['user_id']]
))['c'];

echo json_encode(['notifications' => $notifications, 'unread' => (int)$unread]);
?>
