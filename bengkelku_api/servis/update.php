<?php
require "../config/database.php";
require "../helpers/response.php";

$id          = $_POST['id'];
$nama_servis = $_POST['nama_servis'];
$harga       = $_POST['harga'];
$deskripsi   = $_POST['deskripsi'];

if (!$id || !$nama_servis || !$harga) {
    jsonResponse("error", "Data tidak lengkap", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE jenis_servis 
     SET nama_servis = ?, harga = ?, deskripsi = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "sisi",
    $nama_servis,
    $harga,
    $deskripsi,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    jsonResponse("success", "Jenis servis berhasil diperbarui");
}

jsonResponse("error", "Gagal memperbarui servis", null, 400);
