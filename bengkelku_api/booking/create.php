<?php
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

if (
    empty($_POST['user_id']) ||
    empty($_POST['kendaraan_id']) ||
    empty($_POST['jenis_servis_id']) ||
    empty($_POST['slot_servis_id'])
) {
    jsonResponse("error", "Field tidak lengkap. Wajib: user_id, kendaraan_id, jenis_servis_id, slot_servis_id", null, 400);
}

$user_id = intval($_POST['user_id']);
$kendaraan_id = intval($_POST['kendaraan_id']);
$jenis_servis_id = intval($_POST['jenis_servis_id']);
$slot_servis_id = intval($_POST['slot_servis_id']);

// Get slot info
$slotStmt = mysqli_prepare($conn, "SELECT tanggal, jam_mulai, kapasitas, terpakai FROM slot_servis WHERE id = ?");
if (!$slotStmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}
mysqli_stmt_bind_param($slotStmt, "i", $slot_servis_id);
mysqli_stmt_execute($slotStmt);
$slotResult = mysqli_stmt_get_result($slotStmt);
$slot = mysqli_fetch_assoc($slotResult);

if (!$slot) {
    jsonResponse("error", "Slot tidak ditemukan", null, 404);
}

if ($slot['terpakai'] >= $slot['kapasitas']) {
    jsonResponse("error", "Slot sudah penuh", null, 400);
}
mysqli_stmt_close($slotStmt);

// Get servis info for price
$servisStmt = mysqli_prepare($conn, "SELECT harga FROM jenis_servis WHERE id = ?");
if (!$servisStmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}
mysqli_stmt_bind_param($servisStmt, "i", $jenis_servis_id);
mysqli_stmt_execute($servisStmt);
$servisResult = mysqli_stmt_get_result($servisStmt);
$servis = mysqli_fetch_assoc($servisResult);

if (!$servis) {
    jsonResponse("error", "Servis tidak ditemukan", null, 404);
}
mysqli_stmt_close($servisStmt);

$tanggal_servis = $slot['tanggal'];
$jam_servis = $slot['jam_mulai'];
$total_biaya = $servis['harga'];
$status = 'MENUNGGU';  // Store uppercase for Android compatibility

// Generate nomor antrian
$antrianQuery = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM booking WHERE tanggal_servis = ?");
mysqli_stmt_bind_param($antrianQuery, "s", $tanggal_servis);
mysqli_stmt_execute($antrianQuery);
$antrianResult = mysqli_stmt_get_result($antrianQuery);
$antrianData = mysqli_fetch_assoc($antrianResult);
$nomor_antrian = $antrianData['total'] + 1;
mysqli_stmt_close($antrianQuery);

// Insert booking
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO booking (user_id, kendaraan_id, jenis_servis_id, slot_servis_id, tanggal_servis, jam_servis, nomor_antrian, status, total_biaya)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "iiiissisi", $user_id, $kendaraan_id, $jenis_servis_id, $slot_servis_id, $tanggal_servis, $jam_servis, $nomor_antrian, $status, $total_biaya);

if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);
    
    // Update slot terpakai
    $updateSlot = mysqli_prepare($conn, "UPDATE slot_servis SET terpakai = terpakai + 1 WHERE id = ?");
    mysqli_stmt_bind_param($updateSlot, "i", $slot_servis_id);
    mysqli_stmt_execute($updateSlot);
    mysqli_stmt_close($updateSlot);
    
    // Return booking data with proper types
    $bookingData = [
        "id" => (int) $booking_id,
        "user_id" => (int) $user_id,
        "kendaraan_id" => (int) $kendaraan_id,
        "jenis_servis_id" => (int) $jenis_servis_id,
        "slot_servis_id" => (int) $slot_servis_id,
        "tanggal_servis" => $tanggal_servis,
        "jam_servis" => $jam_servis,
        "nomor_antrian" => (int) $nomor_antrian,
        "status" => $status,  // Already uppercase
        "total_biaya" => (int) $total_biaya
    ];
    
    jsonResponse("success", "Booking berhasil dibuat", $bookingData);
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}