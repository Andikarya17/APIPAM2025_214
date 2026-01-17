<?php
/**
 * AUTH REGISTER - Proper implementation with prepared statements
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Get input
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate
if ($nama === '' || $username === '' || $password === '') {
    jsonResponse("error", "Nama, username, dan password wajib diisi", null, 400);
}

// Check duplicate username
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
if (!$checkStmt) {
    logError("register: check prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($checkStmt, "s", $username);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (mysqli_fetch_assoc($checkResult)) {
    mysqli_stmt_close($checkStmt);
    jsonResponse("error", "Username sudah digunakan", null, 409);
}
mysqli_stmt_close($checkStmt);

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);
$role = "customer";

// Insert user
$stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    logError("register: insert prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $hash, $role);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    jsonResponse("success", "Registrasi berhasil", [
        "id" => (int)$newId,
        "nama" => $nama,
        "username" => $username,
        "role" => $role
    ]);
} else {
    logError("register: insert execute failed", ["error" => mysqli_stmt_error($stmt)]);
    mysqli_stmt_close($stmt);
    jsonResponse("error", "Gagal registrasi", null, 500);
}
