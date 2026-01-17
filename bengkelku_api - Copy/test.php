<?php
/**
 * Test endpoint to verify PHP and database connection
 */
ob_start();

require_once "config/database.php";
require_once "helpers/response.php";

// Test that we got here
jsonResponse("success", "API is working", [
    "php_version" => phpversion(),
    "mysqli_get_result_available" => function_exists('mysqli_stmt_get_result'),
    "db_connected" => isset($conn) && $conn ? true : false
]);
