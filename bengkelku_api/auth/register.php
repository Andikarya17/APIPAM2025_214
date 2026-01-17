<?php
ob_start();
require "../config/database.php";
require "../helpers/response.php";

$nama     = $_POST['nama'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!$nama || !$username || !$password) {
    jsonResponse("error", "Data tidak lengkap", null, 400);
}

// Check if username already exists
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
if (!$checkStmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}
mysqli_stmt_bind_param($checkStmt, "s", $username);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
if (mysqli_fetch_assoc($checkResult)) {
    jsonResponse("error", "Username sudah digunakan", null, 409);
}
mysqli_stmt_close($checkStmt);

$hash = password_hash($password, PASSWORD_BCRYPT);
$role = "customer";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $hash, $role);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    jsonResponse("success", "Registrasi berhasil", ["id" => (int) $newId]);
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}
