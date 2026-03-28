<?php
// Railway provides DATABASE_URL automatically when you add PostgreSQL
// Falls back to local XAMPP config for development
$database_url = getenv('DATABASE_URL');

if ($database_url) {
    // Production (Railway)
    $conn = pg_connect($database_url);
} else {
    // Local development (XAMPP)
    $host     = "localhost";
    $port     = "5432";
    $dbname   = "capstone_db";
    $user     = "postgres";
    $password = "alforte_db";
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
}

if (!$conn) {
    die(json_encode(['error' => 'Database connection failed']));
}
?>
