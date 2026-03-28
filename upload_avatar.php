<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$avatar = $data['avatar'] ?? '';

if (!$avatar) {
    echo json_encode(['success' => false, 'error' => 'No image provided']);
    exit;
}

// Validate it's a base64 image (jpeg, png, webp, gif)
if (!preg_match('/^data:image\/(jpeg|jpg|png|webp|gif);base64,/', $avatar)) {
    echo json_encode(['success' => false, 'error' => 'Invalid image format']);
    exit;
}

// Limit size to ~2MB (base64 is ~1.33x larger than raw)
if (strlen($avatar) > 2 * 1024 * 1024 * 1.37) {
    echo json_encode(['success' => false, 'error' => 'Image too large. Max 2MB.']);
    exit;
}

$result = pg_query_params($conn,
    "UPDATE users SET avatar = $1 WHERE id = $2 RETURNING avatar",
    [$avatar, $_SESSION['user_id']]
);

if ($result && pg_affected_rows($result) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
}
?>
