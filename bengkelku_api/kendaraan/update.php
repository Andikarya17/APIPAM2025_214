<?php
/**
 * KENDARAAN UPDATE - Proper implementation with prepared statements
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
$merk = isset($_POST['merk']) ? trim($_POST['merk']) : '';
$model = isset($_POST['model']) ? trim($_POST['model']) : '';
$nomor_plat = isset($_POST['nomor_plat']) ? strtoupper(trim($_POST['nomor_plat'])) : '';
$tahun = isset($_POST['tahun']) && $_POST['tahun'] !== '' ? (int)$_POST['tahun'] : null;

// Validate
if ($id <= 0 || $merk === '' || $model === '' || $nomor_plat === '') {
    jsonResponse("error", "id, merk, model, nomor_plat wajib diisi", null, 400);
}

// Update with prepared statement
$stmt = mysqli_prepare($conn, "UPDATE kendaraan SET merk = ?, model = ?, nomor_plat = ?, tahun = ? WHERE id = ?");
if (!$stmt) {
    logError("kendaraan/update: prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "sssii", $merk, $model, $nomor_plat, $tahun, $id);

if (!mysqli_stmt_execute($stmt)) {
    logError("kendaraan/update: execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "Database error", null, 500);
}

$affected = mysqli_affected_rows($conn);
mysqli_stmt_close($stmt);

if ($affected > 0) {
    jsonResponse("success", "Kendaraan berhasil diperbarui", null);
} else {
    jsonResponse("error", "Kendaraan tidak ditemukan atau tidak ada perubahan", null, 404);
}
