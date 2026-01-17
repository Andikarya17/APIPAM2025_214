<?php
ob_start();
require "../config/database.php";
require "../helpers/response.php";

// Ambil data dari POST
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi minimal
if ($username === '' || $password === '') {
    jsonResponse("error", "Username dan password wajib diisi", null, 400);
}

// Ambil user
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, nama, username, password, role FROM users WHERE username = ?"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Cek password HASH
if ($user && password_verify($password, $user['password'])) {
    jsonResponse("success", "Login berhasil", [
        "id" => (int) $user['id'],
        "nama" => $user['nama'],
        "username" => $user['username'],
        "role" => $user['role']
    ]);
}

// Gagal login
jsonResponse("error", "Username atau password salah", null, 401);
