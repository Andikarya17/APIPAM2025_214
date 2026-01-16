<?php
require "../config/database.php";
require "../helpers/response.php";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO slot_servis (tanggal, jam, kapasitas)
     VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $_POST['tanggal'],
    $_POST['jam'],
    $_POST['kapasitas']
);

mysqli_stmt_execute($stmt)
    ? jsonResponse("success", "Slot dibuat")
    : jsonResponse("error", "Gagal", null, 400);
