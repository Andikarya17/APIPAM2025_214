<?php
require "../config/database.php";
require "../helpers/response.php";

$nama     = $_POST['nama'];
$username = $_POST['username'];
$password = $_POST['password'];

if (!$nama || !$username || !$password) {
    jsonResponse("error", "Data tidak lengkap", null, 400);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$role = "customer";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $hash, $role);

if (mysqli_stmt_execute($stmt)) {
    jsonResponse("success", "Registrasi berhasil");
} else {
    jsonResponse("error", "Username sudah digunakan", null, 409);
}
