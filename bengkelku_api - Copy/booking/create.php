<?php
/**
 * BOOKING CREATE - NO antrian column in database
 * 
 * Actual booking table columns:
 * - id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, status, created_at
 * 
 * nomor_antrian is COMPUTED at runtime, NOT stored in database
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

// Get and validate input
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$kendaraan_id = isset($_POST['kendaraan_id']) ? (int)$_POST['kendaraan_id'] : 0;
$jenis_servis_id = isset($_POST['jenis_servis_id']) ? (int)$_POST['jenis_servis_id'] : 0;
$slot_servis_id = isset($_POST['slot_servis_id']) ? (int)$_POST['slot_servis_id'] : 0;

logError("booking/create called", [
    "user_id" => $user_id,
    "kendaraan_id" => $kendaraan_id,
    "jenis_servis_id" => $jenis_servis_id,
    "slot_servis_id" => $slot_servis_id
]);

if ($user_id <= 0 || $kendaraan_id <= 0 || $jenis_servis_id <= 0 || $slot_servis_id <= 0) {
    jsonResponse("error", "user_id, kendaraan_id, jenis_servis_id, slot_servis_id wajib > 0", null, 400);
}

// 1. Validate slot exists and has capacity
$slotStmt = mysqli_prepare($conn, "SELECT id, tanggal, jam_mulai, kapasitas, terpakai FROM slot_servis WHERE id = ?");
if (!$slotStmt) {
    logError("slot prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($slotStmt, "i", $slot_servis_id);
mysqli_stmt_execute($slotStmt);
$slotResult = mysqli_stmt_get_result($slotStmt);
$slot = mysqli_fetch_assoc($slotResult);
mysqli_stmt_close($slotStmt);

if (!$slot) {
    jsonResponse("error", "Slot tidak ditemukan", null, 404);
}

if ((int)$slot['terpakai'] >= (int)$slot['kapasitas']) {
    jsonResponse("error", "Slot sudah penuh", null, 400);
}

// 2. Validate jenis_servis exists
$servisStmt = mysqli_prepare($conn, "SELECT id, nama_servis, harga FROM jenis_servis WHERE id = ?");
if (!$servisStmt) {
    logError("servis prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($servisStmt, "i", $jenis_servis_id);
mysqli_stmt_execute($servisStmt);
$servisResult = mysqli_stmt_get_result($servisStmt);
$servis = mysqli_fetch_assoc($servisResult);
mysqli_stmt_close($servisStmt);

if (!$servis) {
    jsonResponse("error", "Servis tidak ditemukan", null, 404);
}

// 3. Insert booking - NO antrian column!
$status = 'MENUNGGU';
$insertQuery = "INSERT INTO booking (user_id, kendaraan_id, jenis_servis_id, slot_servis_id, status) VALUES (?, ?, ?, ?, ?)";
$insertStmt = mysqli_prepare($conn, $insertQuery);
if (!$insertStmt) {
    logError("insert prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($insertStmt, "iiiis", $user_id, $kendaraan_id, $jenis_servis_id, $slot_servis_id, $status);

if (!mysqli_stmt_execute($insertStmt)) {
    logError("insert execute failed", ["error" => mysqli_stmt_error($insertStmt)]);
    jsonResponse("error", "Gagal insert booking: " . mysqli_stmt_error($insertStmt), null, 500);
}

$booking_id = mysqli_insert_id($conn);
mysqli_stmt_close($insertStmt);

// 4. Update slot terpakai
$updateStmt = mysqli_prepare($conn, "UPDATE slot_servis SET terpakai = terpakai + 1 WHERE id = ?");
if ($updateStmt) {
    mysqli_stmt_bind_param($updateStmt, "i", $slot_servis_id);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
}

// 5. Compute nomor_antrian at runtime: COUNT bookings for same slot + position
$antrianStmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM booking WHERE slot_servis_id = ? AND id <= ?");
mysqli_stmt_bind_param($antrianStmt, "ii", $slot_servis_id, $booking_id);
mysqli_stmt_execute($antrianStmt);
$antrianResult = mysqli_stmt_get_result($antrianStmt);
$antrianRow = mysqli_fetch_assoc($antrianResult);
$nomor_antrian = (int)$antrianRow['total'];
mysqli_stmt_close($antrianStmt);

logError("booking created", ["booking_id" => $booking_id, "nomor_antrian" => $nomor_antrian]);

// 6. Return success with computed values
jsonResponse("success", "Booking berhasil dibuat", [
    "id" => (int)$booking_id,
    "user_id" => $user_id,
    "kendaraan_id" => $kendaraan_id,
    "jenis_servis_id" => $jenis_servis_id,
    "slot_servis_id" => $slot_servis_id,
    "nomor_antrian" => $nomor_antrian,  // COMPUTED, not from DB
    "status" => $status,
    "tanggal_servis" => $slot['tanggal'],
    "jam_servis" => $slot['jam_mulai'],
    "total_biaya" => (int)$servis['harga'],
    "nama_servis" => $servis['nama_servis']
]);
