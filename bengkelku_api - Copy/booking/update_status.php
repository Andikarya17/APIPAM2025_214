<?php
/**
 * BOOKING UPDATE STATUS - NO antrian column needed
 * 
 * Actual booking table columns:
 * - id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, status, created_at
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? strtoupper(trim($_POST['status'])) : '';

logError("booking/update_status called", ["id" => $id, "status" => $status]);

if ($id <= 0 || $status === '') {
    jsonResponse("error", "id dan status wajib diisi", null, 400);
}

$allowed = ['MENUNGGU', 'DIPROSES', 'SELESAI', 'DIBATALKAN'];
if (!in_array($status, $allowed)) {
    jsonResponse("error", "Status tidak valid. Gunakan: " . implode(", ", $allowed), null, 400);
}

$stmt = mysqli_prepare($conn, "UPDATE booking SET status = ? WHERE id = ?");
if (!$stmt) {
    logError("update_status prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (!mysqli_stmt_execute($stmt)) {
    logError("update_status execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "DB Error: " . mysqli_stmt_error($stmt), null, 500);
}

$affected = mysqli_affected_rows($conn);
mysqli_stmt_close($stmt);

logError("update_status result", ["affected" => $affected]);

if ($affected > 0) {
    jsonResponse("success", "Status berhasil diperbarui", null);
} else {
    jsonResponse("error", "Booking tidak ditemukan atau status tidak berubah", null, 404);
}
