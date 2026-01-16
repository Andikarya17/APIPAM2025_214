<?php
header("Content-Type: application/json");

require "../config/database.php";
require "../helpers/response.php";

// Ambil data dari POST saja
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi minimal
if ($username === '' || $password === '') {
    jsonResponse(
        "error",
        "Username dan password wajib diisi",
        null,
        400
    );
}

// Ambil user
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, nama, username, password, role FROM users WHERE username = ?"
);

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Cek password HASH (INI KUNCI)
if ($user && password_verify($password, $user['password'])) {
    jsonResponse(
        "success",
        "Login berhasil",
        [
            "id" => $user['id'],
            "nama" => $user['nama'],
            "username" => $user['username'],
            "role" => $user['role']
        ]
    );
}

// Gagal login
jsonResponse(
    "error",
    "Username atau password salah",
    null,
    401
);
