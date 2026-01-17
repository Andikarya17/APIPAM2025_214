<?php
require "../config/database.php";
require "../helpers/response.php";

$user_id = $_GET['user_id'];

if (!$user_id) {
    jsonResponse("error", "User ID tidak valid", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, nama, username, role FROM users WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    jsonResponse("success", "Profil user", $user);
}

jsonResponse("error", "User tidak ditemukan", null, 404);
