<?php
/**
 * BENGKELKU API - Database Configuration
 * 
 * CRITICAL: This file must NOT produce any output or fatal errors
 */

// Suppress display but log to file (if possible)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Try to create logs directory
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/php_error.log');

$host = "localhost";
$user = "root";
$pass = "justachillguy17";
$db   = "bengkelku_db";

$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    @error_log("DB Connection Failed: " . mysqli_connect_error());
    
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed",
        "data" => null
    ]);
    exit;
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
