<?php
require "../config/database.php";
require "../helpers/response.php";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO kendaraan (user_id, merk, model, nomor_plat, tahun, warna)
     VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param(
    $stmt,
    "isssis",
    $_POST['user_id'],
    $_POST['merk'],
    $_POST['model'],
    $_POST['nomor_plat'],
    $_POST['tahun'],
    $_POST['warna']
);

mysqli_stmt_execute($stmt)
    ? jsonResponse("success", "Kendaraan ditambahkan")
    : jsonResponse("error", "Gagal menambah kendaraan", null, 400);
