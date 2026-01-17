<?php
ob_start();
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

if (empty($_POST['nama_servis']) || !isset($_POST['harga'])) {
    jsonResponse("error", "Field tidak lengkap. Wajib: nama_servis, harga", null, 400);
}

$nama_servis = trim($_POST['nama_servis']);
$harga = intval($_POST['harga']);
$deskripsi = isset($_POST['deskripsi']) && $_POST['deskripsi'] !== '' ? trim($_POST['deskripsi']) : null;

if ($harga < 0) {
    jsonResponse("error", "Harga tidak boleh negatif", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO jenis_servis (nama_servis, harga, deskripsi, is_active)
     VALUES (?, ?, ?, 1)"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "sis", $nama_servis, $harga, $deskripsi);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    jsonResponse("success", "Servis berhasil ditambahkan", ["id" => $newId]);
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}