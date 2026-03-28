<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Return timestamp as ISO string — no UTC conversion so JS gets the actual stored time
$sql    = "SELECT id, mood,
                  COALESCE(note, '') AS notes,
                  COALESCE(intensity, 5) AS intensity,
                  COALESCE(triggers, '') AS triggers,
                  COALESCE(activities, '') AS activities,
                  to_char(created_at, 'YYYY-MM-DD\"T\"HH24:MI:SS') AS timestamp
           FROM moods
           WHERE user_id = $1
           ORDER BY created_at DESC";

$result = pg_query_params($conn, $sql, [$_SESSION['user_id']]);

if (!$result) {
    echo json_encode(['error' => pg_last_error($conn)]);
    exit;
}

$moods = [];
while ($row = pg_fetch_assoc($result)) {
    $moods[] = $row;
}

echo json_encode($moods);
?>
