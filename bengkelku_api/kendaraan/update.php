<?php
ob_start();
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

if (
    empty($_POST['id']) ||
    empty($_POST['merk']) ||
    empty($_POST['model']) ||
    empty($_POST['nomor_plat'])
) {
    jsonResponse("error", "Field tidak lengkap. Wajib: id, merk, model, nomor_plat", null, 400);
}

$id = intval($_POST['id']);
$merk = trim($_POST['merk']);
$model = trim($_POST['model']);
$nomor_plat = strtoupper(trim($_POST['nomor_plat']));
$tahun = isset($_POST['tahun']) && $_POST['tahun'] !== '' ? intval($_POST['tahun']) : null;
$warna = isset($_POST['warna']) && $_POST['warna'] !== '' ? trim($_POST['warna']) : null;

$stmt = mysqli_prepare(
    $conn,
    "UPDATE kendaraan 
     SET merk = ?, model = ?, nomor_plat = ?, tahun = ?, warna = ?
     WHERE id = ?"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "sssssi", $merk, $model, $nomor_plat, $tahun, $warna, $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        jsonResponse("success", "Kendaraan berhasil diperbarui", null);
    } else {
        jsonResponse("error", "Kendaraan tidak ditemukan atau tidak ada perubahan", null, 404);
    }
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}