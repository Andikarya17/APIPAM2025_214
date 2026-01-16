<?php
require "../config/database.php";
require "../helpers/response.php";

$id     = $_POST['id'];
$status = $_POST['status'];

$allowed = ['menunggu', 'dalam_proses', 'selesai', 'diambil'];

if (!$id || !in_array($status, $allowed)) {
    jsonResponse("error", "Status tidak valid", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE booking SET status = ? WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (mysqli_stmt_execute($stmt)) {
    jsonResponse("success", "Status booking diperbarui");
}

jsonResponse("error", "Gagal update status", null, 400);
