<?php
/**
 * AUTH LOGIN - Proper implementation with prepared statements
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Get input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate
if ($username === '' || $password === '') {
    jsonResponse("error", "Username dan password wajib diisi", null, 400);
}

// Query user
$stmt = mysqli_prepare($conn, "SELECT id, nama, username, password, role FROM users WHERE username = ?");
if (!$stmt) {
    logError("login: prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "s", $username);

if (!mysqli_stmt_execute($stmt)) {
    logError("login: execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "Database error", null, 500);
}

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Verify password
if ($user && password_verify($password, $user['password'])) {
    jsonResponse("success", "Login berhasil", [
        "id" => (int)$user['id'],
        "nama" => $user['nama'],
        "username" => $user['username'],
        "role" => $user['role']
    ]);
}

// Failed login
jsonResponse("error", "Username atau password salah", null, 401);
