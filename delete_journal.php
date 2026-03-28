<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Support both DELETE method (query string) and POST body
$id = $_GET['id'] ?? null;
if (!$id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = $data['id'] ?? null;
}

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing entry ID']);
    exit;
}

$sql    = "DELETE FROM journal WHERE id = $1 AND user_id = $2";
$result = pg_query_params($conn, $sql, [$id, $_SESSION['user_id']]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
}
?>
