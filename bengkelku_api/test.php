<?php
// Test endpoint to verify database and PHP are working
ob_start();
require "config/database.php";
require "helpers/response.php";

jsonResponse("success", "API is working", [
    "php_version" => phpversion(),
    "db_connected" => $conn ? true : false
]);
