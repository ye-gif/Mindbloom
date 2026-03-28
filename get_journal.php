<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sql = "SELECT 
            id::text,
            title,
            content,
            COALESCE(mood, '') AS mood,
            to_char(created_at, 'YYYY-MM-DD\"T\"HH24:MI:SS') AS timestamp
        FROM journal
        WHERE user_id = $1
        ORDER BY created_at DESC";

$result = pg_query_params($conn, $sql, [$_SESSION['user_id']]);

if (!$result) {
    echo json_encode(['error' => pg_last_error($conn)]);
    exit;
}

$entries = [];
while ($row = pg_fetch_assoc($result)) {
    $entries[] = $row;
}

echo json_encode($entries);
?>
