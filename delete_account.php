<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';

if (!$password) {
    echo json_encode(['success' => false, 'error' => 'Password is required to delete account']);
    exit;
}

// Verify password before deletion
$result = pg_query_params($conn, "SELECT password FROM users WHERE id = $1", [$_SESSION['user_id']]);
$user   = pg_fetch_assoc($result);

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Incorrect password']);
    exit;
}

// Delete user — cascades to moods, journal, settings via FK
$delete = pg_query_params($conn, "DELETE FROM users WHERE id = $1", [$_SESSION['user_id']]);

if ($delete) {
    session_destroy();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
}
?>
