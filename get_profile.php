<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$result = pg_query_params($conn,
    "SELECT username, email, avatar, created_at FROM users WHERE id = $1",
    [$_SESSION['user_id']]
);

if ($result) {
    $row = pg_fetch_assoc($result);
    echo json_encode($row);
} else {
    echo json_encode(['error' => pg_last_error($conn)]);
}
?>
