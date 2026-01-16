<?php
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
    exit;
}

if (
    empty($_POST['user_id']) ||
    empty($_POST['kendaraan_id']) ||
    empty($_POST['servis_id']) ||
    empty($_POST['slot_servis_id'])
) {
    jsonResponse("error", "Field tidak lengkap. Wajib: user_id, kendaraan_id, servis_id, slot_servis_id", null, 400);
    exit;
}

$user_id = intval($_POST['user_id']);
$kendaraan_id = intval($_POST['kendaraan_id']);
$servis_id = intval($_POST['servis_id']);
$slot_servis_id = intval($_POST['slot_servis_id']);

// Get slot info
$slotQuery = mysqli_prepare($conn, "SELECT tanggal, jam_mulai, kapasitas, terpakai FROM slot_servis WHERE id = ?");
mysqli_stmt_bind_param($slotQuery, "i", $slot_servis_id);
mysqli_stmt_execute($slotQuery);
$slotResult = mysqli_stmt_get_result($slotQuery);
$slot = mysqli_fetch_assoc($slotResult);

if (!$slot) {
    jsonResponse("error", "Slot tidak ditemukan", null, 404);
    exit;
}

if ($slot['terpakai'] >= $slot['kapasitas']) {
    jsonResponse("error", "Slot sudah penuh", null, 400);
    exit;
}

// Get servis info for price
$servisQuery = mysqli_prepare($conn, "SELECT harga FROM servis WHERE id = ?");
mysqli_stmt_bind_param($servisQuery, "i", $servis_id);
mysqli_stmt_execute($servisQuery);
$servisResult = mysqli_stmt_get_result($servisQuery);
$servis = mysqli_fetch_assoc($servisResult);

if (!$servis) {
    jsonResponse("error", "Servis tidak ditemukan", null, 404);
    exit;
}

$tanggal_servis = $slot['tanggal'];
$jam_servis = $slot['jam_mulai'];
$total_biaya = $servis['harga'];
$status = 'MENUNGGU';

// Generate nomor antrian
$antrianQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM booking WHERE tanggal_servis = '$tanggal_servis'");
$antrianData = mysqli_fetch_assoc($antrianQuery);
$nomor_antrian = $antrianData['total'] + 1;

// Insert booking
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO booking (pengguna_id, kendaraan_id, servis_id, slot_servis_id, tanggal_servis, jam_servis, status, total_biaya, nomor_antrian)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
    exit;
}

mysqli_stmt_bind_param($stmt, "iiiisssii", $user_id, $kendaraan_id, $servis_id, $slot_servis_id, $tanggal_servis, $jam_servis, $status, $total_biaya, $nomor_antrian);

if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);
    
    // Update slot terpakai
    mysqli_query($conn, "UPDATE slot_servis SET terpakai = terpakai + 1 WHERE id = $slot_servis_id");
    
    // Return booking data
    $bookingData = [
        "id" => $booking_id,
        "pengguna_id" => $user_id,
        "kendaraan_id" => $kendaraan_id,
        "servis_id" => $servis_id,
        "slot_servis_id" => $slot_servis_id,
        "tanggal_servis" => $tanggal_servis,
        "jam_servis" => $jam_servis,
        "status" => $status,
        "total_biaya" => $total_biaya,
        "nomor_antrian" => $nomor_antrian
    ];
    
    jsonResponse("success", "Booking berhasil dibuat", $bookingData);
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 400);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);