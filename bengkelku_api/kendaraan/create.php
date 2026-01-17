<?php
/**
 * KENDARAAN CREATE - Based on ACTUAL DB schema
 * 
 * kendaraan table columns:
 * - id (auto)
 * - user_id (NOT NULL)
 * - merk (NOT NULL)
 * - model (NOT NULL)
 * - nomor_plat (NOT NULL)
 * - tahun
 * - warna (nullable)
 * - created_at (auto)
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

// Get input
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$merk = isset($_POST['merk']) ? trim($_POST['merk']) : '';
$model = isset($_POST['model']) ? trim($_POST['model']) : '';
$nomor_plat = isset($_POST['nomor_plat']) ? strtoupper(trim($_POST['nomor_plat'])) : '';
$tahun = isset($_POST['tahun']) && $_POST['tahun'] !== '' ? (int)$_POST['tahun'] : null;
$warna = isset($_POST['warna']) && $_POST['warna'] !== '' ? trim($_POST['warna']) : null;

logError("kendaraan/create called", [
    "user_id" => $user_id,
    "merk" => $merk,
    "model" => $model,
    "nomor_plat" => $nomor_plat,
    "tahun" => $tahun,
    "warna" => $warna
]);

// Validate required fields
if ($user_id <= 0) {
    jsonResponse("error", "user_id wajib diisi dan > 0", null, 400);
}
if ($merk === '') {
    jsonResponse("error", "merk wajib diisi", null, 400);
}
if ($model === '') {
    jsonResponse("error", "model wajib diisi", null, 400);
}
if ($nomor_plat === '') {
    jsonResponse("error", "nomor_plat wajib diisi", null, 400);
}

// Check if user exists
$userCheckQuery = "SELECT id FROM users WHERE id = ?";
$userCheckStmt = mysqli_prepare($conn, $userCheckQuery);
if (!$userCheckStmt) {
    logError("user check prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($userCheckStmt, "i", $user_id);
mysqli_stmt_execute($userCheckStmt);
$userResult = mysqli_stmt_get_result($userCheckStmt);
$userExists = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($userCheckStmt);

if (!$userExists) {
    jsonResponse("error", "User dengan ID $user_id tidak ditemukan", null, 404);
}

// Check duplicate nomor_plat for this user
$dupCheckQuery = "SELECT id FROM kendaraan WHERE user_id = ? AND nomor_plat = ?";
$dupCheckStmt = mysqli_prepare($conn, $dupCheckQuery);
if (!$dupCheckStmt) {
    logError("duplicate check prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($dupCheckStmt, "is", $user_id, $nomor_plat);
mysqli_stmt_execute($dupCheckStmt);
$dupResult = mysqli_stmt_get_result($dupCheckStmt);
$duplicate = mysqli_fetch_assoc($dupResult);
mysqli_stmt_close($dupCheckStmt);

if ($duplicate) {
    jsonResponse("error", "Kendaraan dengan nomor plat $nomor_plat sudah terdaftar", null, 409);
}

// Insert kendaraan
// Columns: user_id, merk, model, nomor_plat, tahun, warna
$insertQuery = "INSERT INTO kendaraan (user_id, merk, model, nomor_plat, tahun, warna) VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = mysqli_prepare($conn, $insertQuery);
if (!$insertStmt) {
    logError("insert prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

// Bind: user_id(i), merk(s), model(s), nomor_plat(s), tahun(i), warna(s)
mysqli_stmt_bind_param($insertStmt, "isssis", 
    $user_id, 
    $merk, 
    $model, 
    $nomor_plat, 
    $tahun, 
    $warna
);

if (!mysqli_stmt_execute($insertStmt)) {
    $error = mysqli_stmt_error($insertStmt);
    logError("insert execute failed", ["error" => $error]);
    jsonResponse("error", "Gagal menambahkan kendaraan: $error", null, 500);
}

$kendaraan_id = mysqli_insert_id($conn);
mysqli_stmt_close($insertStmt);

logError("kendaraan created", ["id" => $kendaraan_id]);

// Return success
jsonResponse("success", "Kendaraan berhasil ditambahkan", [
    "id" => (int)$kendaraan_id,
    "user_id" => $user_id,
    "merk" => $merk,
    "model" => $model,
    "nomor_plat" => $nomor_plat,
    "tahun" => $tahun,
    "warna" => $warna
]);
