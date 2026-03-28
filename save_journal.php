<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid request body']);
    exit;
}

$title   = trim($data['title']   ?? '');
$content = trim($data['content'] ?? '');
$mood    = (isset($data['mood']) && $data['mood'] !== '' && $data['mood'] !== null)
           ? trim($data['mood']) : null;

// Use client timestamp if provided
$clientTs = $data['timestamp'] ?? null;
if ($clientTs) {
    $dt = new DateTime($clientTs, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Manila'));
    $timestamp = $dt->format('Y-m-d H:i:s');
} else {
    $dt = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $timestamp = $dt->format('Y-m-d H:i:s');
}

if ($title === '') {
    echo json_encode(['success' => false, 'error' => 'Title is required']);
    exit;
}
if ($content === '') {
    echo json_encode(['success' => false, 'error' => 'Content is required']);
    exit;
}

if ($mood !== null) {
    $result = pg_query_params($conn,
        "INSERT INTO journal (user_id, title, content, mood, created_at)
         VALUES ($1, $2, $3, $4, $5) RETURNING id",
        [$_SESSION['user_id'], $title, $content, $mood, $timestamp]
    );
} else {
    $result = pg_query_params($conn,
        "INSERT INTO journal (user_id, title, content, created_at)
         VALUES ($1, $2, $3, $4) RETURNING id",
        [$_SESSION['user_id'], $title, $content, $timestamp]
    );
}

if ($result) {
    $row = pg_fetch_assoc($result);
    echo json_encode(['success' => true, 'id' => $row['id']]);
} else {
    echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
}
?>
