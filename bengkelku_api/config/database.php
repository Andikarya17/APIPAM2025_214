<?php
// Suppress PHP notices and warnings from being output
error_reporting(E_ALL);
ini_set('display_errors', 0);

$host = "localhost";
$user = "root";
$pass = "justachillguy17";
$db   = "bengkelku_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . mysqli_connect_error(),
        "data" => null
    ]);
    exit;
}
