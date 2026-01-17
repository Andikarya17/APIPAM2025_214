<?php
/**
 * SERVIS UPDATE - Proper implementation with prepared statements
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

// Get input
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nama_servis = isset($_POST['nama_servis']) ? trim($_POST['nama_servis']) : '';
$harga = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
$deskripsi = isset($_POST['deskripsi']) && $_POST['deskripsi'] !== '' ? trim($_POST['deskripsi']) : null;

// Validate
if ($id <= 0 || $nama_servis === '' || $harga <= 0) {
    jsonResponse("error", "id, nama_servis, harga wajib diisi", null, 400);
}

// Update with prepared statement
$stmt = mysqli_prepare($conn, "UPDATE jenis_servis SET nama_servis = ?, harga = ?, deskripsi = ? WHERE id = ?");
if (!$stmt) {
    logError("servis/update: prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "sisi", $nama_servis, $harga, $deskripsi, $id);

if (!mysqli_stmt_execute($stmt)) {
    logError("servis/update: execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "Database error", null, 500);
}

$affected = mysqli_affected_rows($conn);
mysqli_stmt_close($stmt);

if ($affected > 0) {
    jsonResponse("success", "Servis berhasil diperbarui", null);
} else {
    jsonResponse("error", "Servis tidak ditemukan atau tidak ada perubahan", null, 404);
}
