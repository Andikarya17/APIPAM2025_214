<?php
require "../config/database.php";
require "../helpers/response.php";

$stmt = mysqli_prepare(
    $conn,
    "UPDATE kendaraan SET merk=?, model=?, nomor_plat=?, tahun=?, warna=? WHERE id=?"
);
mysqli_stmt_bind_param(
    $stmt,
    "sssisi",
    $_POST['merk'],
    $_POST['model'],
    $_POST['nomor_plat'],
    $_POST['tahun'],
    $_POST['warna'],
    $_POST['id']
);

mysqli_stmt_execute($stmt)
    ? jsonResponse("success", "Kendaraan diperbarui")
    : jsonResponse("error", "Update gagal", null, 400);
