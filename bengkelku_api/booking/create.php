<?php
require "../config/database.php";
require "../helpers/response.php";

$nomor = uniqid("A");

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO booking
     (user_id, kendaraan_id, jenis_servis_id, slot_servis_id, nomor_antrian)
     VALUES (?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param(
    $stmt,
    "iiiis",
    $_POST['user_id'],
    $_POST['kendaraan_id'],
    $_POST['jenis_servis_id'],
    $_POST['slot_servis_id'],
    $nomor
);

mysqli_stmt_execute($stmt)
    ? jsonResponse("success", "Booking berhasil", ["nomor_antrian"=>$nomor])
    : jsonResponse("error", "Slot penuh / gagal", null, 400);
