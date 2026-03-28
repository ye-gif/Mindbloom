<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$id   = $data['id'] ?? null; // null = mark all read

if ($id) {
    pg_query_params($conn,
        "UPDATE notifications SET is_read = TRUE WHERE id = $1 AND user_id = $2",
        [$id, $_SESSION['user_id']]
    );
} else {
    pg_query_params($conn,
        "UPDATE notifications SET is_read = TRUE WHERE user_id = $1",
        [$_SESSION['user_id']]
    );
}

echo json_encode(['success' => true]);
?>
