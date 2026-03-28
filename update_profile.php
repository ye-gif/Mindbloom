<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$email    = trim($data['email']    ?? '');

if ($username === '') {
    echo json_encode(['success' => false, 'error' => 'Username cannot be empty']);
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit;
}

// Check if username is taken by another user
$check = pg_query_params($conn,
    "SELECT id FROM users WHERE username = $1 AND id != $2",
    [$username, $_SESSION['user_id']]
);
if ($check && pg_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'error' => 'Username already taken']);
    exit;
}

// Check if email is taken by another user
if ($email !== '') {
    $checkEmail = pg_query_params($conn,
        "SELECT id FROM users WHERE email = $1 AND id != $2",
        [$email, $_SESSION['user_id']]
    );
    if ($checkEmail && pg_num_rows($checkEmail) > 0) {
        echo json_encode(['success' => false, 'error' => 'Email already in use']);
        exit;
    }
}

// Build update query dynamically
if ($email !== '') {
    $result = pg_query_params($conn,
        "UPDATE users SET username = $1, email = $2 WHERE id = $3 RETURNING username, email",
        [$username, $email, $_SESSION['user_id']]
    );
} else {
    $result = pg_query_params($conn,
        "UPDATE users SET username = $1 WHERE id = $2 RETURNING username, email",
        [$username, $_SESSION['user_id']]
    );
}

if ($result && pg_affected_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    // Update session so the sidebar name updates immediately
    $_SESSION['username'] = $row['username'];
    echo json_encode(['success' => true, 'username' => $row['username'], 'email' => $row['email']]);
} else {
    echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
}
?>
