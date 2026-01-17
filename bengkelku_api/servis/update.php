<?php
ob_start();
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

if (empty($_POST['id']) || empty($_POST['nama_servis']) || !isset($_POST['harga'])) {
    jsonResponse("error", "Field tidak lengkap. Wajib: id, nama_servis, harga", null, 400);
}

$id = intval($_POST['id']);
$nama_servis = trim($_POST['nama_servis']);
$harga = intval($_POST['harga']);
$deskripsi = isset($_POST['deskripsi']) && $_POST['deskripsi'] !== '' ? trim($_POST['deskripsi']) : null;
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

$stmt = mysqli_prepare(
    $conn,
    "UPDATE jenis_servis 
     SET nama_servis = ?, harga = ?, deskripsi = ?, is_active = ?
     WHERE id = ?"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "sisii", $nama_servis, $harga, $deskripsi, $is_active, $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        jsonResponse("success", "Servis berhasil diperbarui", null);
    } else {
        jsonResponse("error", "Servis tidak ditemukan atau tidak ada perubahan", null, 404);
    }
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}