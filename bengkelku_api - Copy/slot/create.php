<?php
/**
 * SLOT CREATE - Proper implementation with prepared statements
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

// Get input
$tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
$jam_mulai = isset($_POST['jam_mulai']) ? trim($_POST['jam_mulai']) : '';
$jam_selesai = isset($_POST['jam_selesai']) ? trim($_POST['jam_selesai']) : '';
$kapasitas = isset($_POST['kapasitas']) ? (int)$_POST['kapasitas'] : 0;

// Validate
if ($tanggal === '' || $jam_mulai === '' || $jam_selesai === '' || $kapasitas <= 0) {
    jsonResponse("error", "tanggal, jam_mulai, jam_selesai, kapasitas wajib diisi", null, 400);
}

// Insert with prepared statement
$stmt = mysqli_prepare($conn, "INSERT INTO slot_servis (tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status) VALUES (?, ?, ?, ?, 0, 'available')");
if (!$stmt) {
    logError("slot/create: prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "sssi", $tanggal, $jam_mulai, $jam_selesai, $kapasitas);

if (!mysqli_stmt_execute($stmt)) {
    logError("slot/create: execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "Database error", null, 500);
}

$newId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

jsonResponse("success", "Slot berhasil dibuat", ["id" => (int)$newId]);
