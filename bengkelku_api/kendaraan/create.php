<?php
ob_start();
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

if (
    empty($_POST['user_id']) ||
    empty($_POST['merk']) ||
    empty($_POST['model']) ||
    empty($_POST['nomor_plat'])
) {
    jsonResponse("error", "Field tidak lengkap. Wajib: user_id, merk, model, nomor_plat", null, 400);
}

$user_id = intval($_POST['user_id']);
$merk = trim($_POST['merk']);
$model = trim($_POST['model']);
$nomor_plat = strtoupper(trim($_POST['nomor_plat']));
$tahun = isset($_POST['tahun']) && $_POST['tahun'] !== '' ? intval($_POST['tahun']) : null;
$warna = isset($_POST['warna']) && $_POST['warna'] !== '' ? trim($_POST['warna']) : null;

// Check duplicate nomor_plat for this user
$checkStmt = mysqli_prepare($conn, "SELECT id FROM kendaraan WHERE user_id = ? AND nomor_plat = ?");
if (!$checkStmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}
mysqli_stmt_bind_param($checkStmt, "is", $user_id, $nomor_plat);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (mysqli_fetch_assoc($checkResult)) {
    jsonResponse("error", "Nomor plat sudah terdaftar untuk akun ini", null, 400);
}
mysqli_stmt_close($checkStmt);

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO kendaraan (user_id, merk, model, nomor_plat, tahun, warna)
     VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "isssss", $user_id, $merk, $model, $nomor_plat, $tahun, $warna);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    jsonResponse("success", "Kendaraan berhasil ditambahkan", ["id" => $newId]);
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}