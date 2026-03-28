<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data        = json_decode(file_get_contents('php://input'), true);
$current     = $data['current_password'] ?? '';
$newPass     = $data['new_password']     ?? '';
$confirmPass = $data['confirm_password'] ?? '';

// Validate inputs
if (!$current || !$newPass || !$confirmPass) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}
if (strlen($newPass) < 6) {
    echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters']);
    exit;
}
if ($newPass !== $confirmPass) {
    echo json_encode(['success' => false, 'error' => 'New passwords do not match']);
    exit;
}
if ($current === $newPass) {
    echo json_encode(['success' => false, 'error' => 'New password must be different from current password']);
    exit;
}

// Fetch current hashed password from DB
$result = pg_query_params($conn, "SELECT password FROM users WHERE id = $1", [$_SESSION['user_id']]);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . pg_last_error($conn)]);
    exit;
}

$user = pg_fetch_assoc($result);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Verify current password against stored hash
if (!password_verify($current, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
    exit;
}

// Hash new password and update
$hashed = password_hash($newPass, PASSWORD_DEFAULT);
$update = pg_query_params($conn,
    "UPDATE users SET password = $1 WHERE id = $2 RETURNING id",
    [$hashed, $_SESSION['user_id']]
);

if ($update && pg_num_rows($update) > 0) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update: ' . pg_last_error($conn)]);
}
?>
