<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data       = json_decode(file_get_contents('php://input'), true);
$mood       = $data['mood']       ?? null;
$note       = $data['notes']      ?? '';
$intensity  = $data['intensity']  ?? 5;
$triggers   = $data['triggers']   ?? '';
$activities = $data['activities'] ?? '';

// Use client-provided timestamp if valid, otherwise fallback to NOW()
$clientTs = $data['timestamp'] ?? null;
if ($clientTs) {
    // Parse ISO 8601 from browser, convert to PH local time (UTC+8)
    $dt = new DateTime($clientTs, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Manila'));
    $timestamp = $dt->format('Y-m-d H:i:s');
} else {
    // Fallback: use server time in PH timezone
    $dt = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $timestamp = $dt->format('Y-m-d H:i:s');
}

if (!$mood) {
    echo json_encode(['error' => 'Mood required']);
    exit;
}

$sql    = "INSERT INTO moods (user_id, mood, note, intensity, triggers, activities, created_at)
           VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id";
$result = pg_query_params($conn, $sql, [
    $_SESSION['user_id'],
    $mood,
    $note,
    $intensity,
    $triggers,
    $activities,
    $timestamp
]);

if ($result) {
    $row = pg_fetch_assoc($result);
    echo json_encode(['success' => true, 'mood_id' => $row['id']]);
} else {
    echo json_encode(['error' => pg_last_error($conn)]);
}
?>
