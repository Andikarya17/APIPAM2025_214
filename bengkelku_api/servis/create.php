<?php
/**
 * SERVIS CREATE - Proper implementation with prepared statements
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

// Get input
$nama_servis = isset($_POST['nama_servis']) ? trim($_POST['nama_servis']) : '';
$harga = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
$deskripsi = isset($_POST['deskripsi']) && $_POST['deskripsi'] !== '' ? trim($_POST['deskripsi']) : null;

// Validate
if ($nama_servis === '' || $harga <= 0) {
    jsonResponse("error", "nama_servis dan harga wajib diisi", null, 400);
}

// Insert with prepared statement
$stmt = mysqli_prepare($conn, "INSERT INTO jenis_servis (nama_servis, harga, deskripsi, is_active) VALUES (?, ?, ?, 1)");
if (!$stmt) {
    logError("servis/create: prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "sis", $nama_servis, $harga, $deskripsi);

if (!mysqli_stmt_execute($stmt)) {
    logError("servis/create: execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "Database error", null, 500);
}

$newId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

jsonResponse("success", "Servis berhasil ditambahkan", ["id" => (int)$newId]);
