<?php
/**
 * SLOT UPDATE - Proper implementation with prepared statements
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
$tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
$jam_mulai = isset($_POST['jam_mulai']) ? trim($_POST['jam_mulai']) : '';
$jam_selesai = isset($_POST['jam_selesai']) ? trim($_POST['jam_selesai']) : '';
$kapasitas = isset($_POST['kapasitas']) ? (int)$_POST['kapasitas'] : 0;

// Validate
if ($id <= 0 || $tanggal === '' || $jam_mulai === '' || $jam_selesai === '' || $kapasitas <= 0) {
    jsonResponse("error", "id, tanggal, jam_mulai, jam_selesai, kapasitas wajib diisi", null, 400);
}

// Update with prepared statement
$stmt = mysqli_prepare($conn, "UPDATE slot_servis SET tanggal = ?, jam_mulai = ?, jam_selesai = ?, kapasitas = ? WHERE id = ?");
if (!$stmt) {
    logError("slot/update: prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "sssii", $tanggal, $jam_mulai, $jam_selesai, $kapasitas, $id);

if (!mysqli_stmt_execute($stmt)) {
    logError("slot/update: execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "Database error", null, 500);
}

$affected = mysqli_affected_rows($conn);
mysqli_stmt_close($stmt);

if ($affected > 0) {
    jsonResponse("success", "Slot berhasil diperbarui", null);
} else {
    jsonResponse("error", "Slot tidak ditemukan atau tidak ada perubahan", null, 404);
}
