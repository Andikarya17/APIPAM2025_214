<?php
require "../config/database.php";
require "../helpers/response.php";

$id = $_POST['id'];

if (!$id) {
    jsonResponse("error", "ID tidak valid", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM jenis_servis WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    jsonResponse("success", "Jenis servis berhasil dihapus");
}

jsonResponse("error", "Gagal menghapus servis", null, 400);
