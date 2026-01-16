<?php
require "../config/database.php";
require "../helpers/response.php";

$id        = $_POST['id'];
$tanggal   = $_POST['tanggal'];
$jam       = $_POST['jam'];
$kapasitas = $_POST['kapasitas'];

if (!$id || !$tanggal || !$jam || !$kapasitas) {
    jsonResponse("error", "Data tidak lengkap", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE slot_servis 
     SET tanggal = ?, jam = ?, kapasitas = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssii",
    $tanggal,
    $jam,
    $kapasitas,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    jsonResponse("success", "Slot servis diperbarui");
}

jsonResponse("error", "Gagal update slot", null, 400);
