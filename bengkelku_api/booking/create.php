<?php
/**
 * BOOKING CREATE - Based on ACTUAL DB schema
 * 
 * booking table columns:
 * - id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, nomot_antrian, status, created_at
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
// slot_servis columns: id, tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status
$slotQuery = "SELECT id, tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status FROM slot_servis WHERE id = ?";
$slotStmt = mysqli_prepare($conn, $slotQuery);
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
    jsonResponse("error", "Slot dengan ID $slot_servis_id tidak ditemukan", null, 404);
}

$terpakai = (int)$slot['terpakai'];
$kapasitas = (int)$slot['kapasitas'];
if ($terpakai >= $kapasitas) {
    jsonResponse("error", "Slot sudah penuh ($terpakai/$kapasitas)", null, 400);
}

// 2. Validate jenis_servis exists
// jenis_servis columns: id, nama_servis, harga, deskripsi, is_active
$servisQuery = "SELECT id, nama_servis, harga FROM jenis_servis WHERE id = ?";
$servisStmt = mysqli_prepare($conn, $servisQuery);
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
    jsonResponse("error", "Servis dengan ID $jenis_servis_id tidak ditemukan", null, 404);
}

// 3. Generate nomot_antrian (typo in DB, but we must use it as-is)
$tanggal = $slot['tanggal'];
$antrianQuery = "SELECT COUNT(*) as total FROM booking b 
                 INNER JOIN slot_servis s ON b.slot_servis_id = s.id 
                 WHERE s.tanggal = ?";
$antrianStmt = mysqli_prepare($conn, $antrianQuery);
if (!$antrianStmt) {
    logError("antrian prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($antrianStmt, "s", $tanggal);
mysqli_stmt_execute($antrianStmt);
$antrianResult = mysqli_stmt_get_result($antrianStmt);
$antrian = mysqli_fetch_assoc($antrianResult);
$nomot_antrian = ($antrian ? (int)$antrian['total'] : 0) + 1;
mysqli_stmt_close($antrianStmt);

// 4. Insert booking
// booking columns: id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, nomot_antrian, status, created_at
$status = 'MENUNGGU';
$insertQuery = "INSERT INTO booking (user_id, kendaraan_id, jenis_servis_id, slot_servis_id, nomot_antrian, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = mysqli_prepare($conn, $insertQuery);
if (!$insertStmt) {
    logError("insert prepare failed", ["error" => mysqli_error($conn), "query" => $insertQuery]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

// Bind: user_id(i), kendaraan_id(i), jenis_servis_id(i), slot_servis_id(i), nomot_antrian(i), status(s)
mysqli_stmt_bind_param($insertStmt, "iiiis", 
    $user_id, 
    $kendaraan_id, 
    $jenis_servis_id, 
    $slot_servis_id, 
    $nomot_antrian, 
    $status
);

if (!mysqli_stmt_execute($insertStmt)) {
    $error = mysqli_stmt_error($insertStmt);
    logError("insert execute failed", ["error" => $error]);
    jsonResponse("error", "Gagal insert booking: $error", null, 500);
}

$booking_id = mysqli_insert_id($conn);
mysqli_stmt_close($insertStmt);

// 5. Update slot terpakai
$updateQuery = "UPDATE slot_servis SET terpakai = terpakai + 1 WHERE id = ?";
$updateStmt = mysqli_prepare($conn, $updateQuery);
if ($updateStmt) {
    mysqli_stmt_bind_param($updateStmt, "i", $slot_servis_id);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
}

logError("booking created", ["booking_id" => $booking_id]);

// 6. Return success with computed values from JOINs
jsonResponse("success", "Booking berhasil dibuat", [
    "id" => (int)$booking_id,
    "user_id" => $user_id,
    "kendaraan_id" => $kendaraan_id,
    "jenis_servis_id" => $jenis_servis_id,
    "slot_servis_id" => $slot_servis_id,
    "nomor_antrian" => $nomot_antrian,  // Return as nomor_antrian for Android
    "status" => $status,
    "tanggal_servis" => $slot['tanggal'],
    "jam_servis" => $slot['jam_mulai'],
    "total_biaya" => (int)$servis['harga'],
    "nama_servis" => $servis['nama_servis']
]);
