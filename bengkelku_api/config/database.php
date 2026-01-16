<?php
$host = "localhost";
$user = "root";
$pass = "justachillguy17";
$db   = "bengkelku_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}
