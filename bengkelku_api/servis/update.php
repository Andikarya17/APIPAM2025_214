<?php
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
    exit;
}

if (empty($_POST['id']) || empty($_POST['nama_servis']) || !isset($_POST['harga'])) {
    jsonResponse("error", "Field tidak lengkap. Wajib: id, nama_servis, harga", null, 400);
    exit;
}

$id = intval($_POST['id']);
$nama_servis = $_POST['nama_servis'];
$harga = intval($_POST['harga']);
$deskripsi = isset($_POST['deskripsi']) && $_POST['deskripsi'] !== '' ? $_POST['deskripsi'] : null;

$stmt = mysqli_prepare(
    $conn,
    "UPDATE servis 
     SET nama_servis = ?, harga = ?, deskripsi = ?
     WHERE id = ?"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
    exit;
}

mysqli_stmt_bind_param($stmt, "sisi", $nama_servis, $harga, $deskripsi, $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        jsonResponse("success", "Servis berhasil diperbarui", null);
    } else {
        jsonResponse("error", "Servis tidak ditemukan atau tidak ada perubahan", null, 404);
    }
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 400);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);