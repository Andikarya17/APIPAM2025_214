<?php
require "../config/database.php";
require "../helpers/response.php";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO jenis_servis (nama_servis, harga, deskripsi)
     VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param(
    $stmt,
    "sis",
    $_POST['nama_servis'],
    $_POST['harga'],
    $_POST['deskripsi']
);

mysqli_stmt_execute($stmt)
    ? jsonResponse("success", "Servis ditambahkan")
    : jsonResponse("error", "Gagal", null, 400);
