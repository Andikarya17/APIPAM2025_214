<?php
/**
 * BOOKING LIST CUSTOMER - NO antrian column in database
 * 
 * Actual booking table columns:
 * - id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, status, created_at
 * 
 * nomor_antrian is COMPUTED using subquery
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

logError("booking/list_customer called", ["user_id" => $user_id]);

if ($user_id <= 0) {
    jsonResponse("error", "user_id wajib diisi", null, 400);
}

// Query WITHOUT antrian column - we compute it in PHP
$query = "
    SELECT 
        b.id,
        b.user_id,
        b.kendaraan_id,
        b.jenis_servis_id,
        b.slot_servis_id,
        b.status,
        b.created_at,
        k.merk,
        k.model,
        k.nomor_plat,
        j.nama_servis,
        j.harga,
        s.tanggal,
        s.jam_mulai,
        s.jam_selesai
    FROM booking b
    LEFT JOIN kendaraan k ON b.kendaraan_id = k.id
    LEFT JOIN jenis_servis j ON b.jenis_servis_id = j.id
    LEFT JOIN slot_servis s ON b.slot_servis_id = s.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    logError("list_customer prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "i", $user_id);

if (!mysqli_stmt_execute($stmt)) {
    logError("list_customer execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "DB Error: " . mysqli_stmt_error($stmt), null, 500);
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    logError("list_customer get_result failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "DB Error: " . mysqli_error($conn), null, 500);
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Compute nomor_antrian: position of this booking within its slot
    $antrianQuery = "SELECT COUNT(*) as pos FROM booking WHERE slot_servis_id = ? AND id <= ?";
    $antrianStmt = mysqli_prepare($conn, $antrianQuery);
    mysqli_stmt_bind_param($antrianStmt, "ii", $row['slot_servis_id'], $row['id']);
    mysqli_stmt_execute($antrianStmt);
    $antrianResult = mysqli_stmt_get_result($antrianStmt);
    $antrianRow = mysqli_fetch_assoc($antrianResult);
    $nomor_antrian = (int)$antrianRow['pos'];
    mysqli_stmt_close($antrianStmt);

    $item = [
        "id" => (int)$row['id'],
        "user_id" => (int)$row['user_id'],
        "kendaraan_id" => (int)$row['kendaraan_id'],
        "jenis_servis_id" => (int)$row['jenis_servis_id'],
        "slot_servis_id" => (int)$row['slot_servis_id'],
        "nomor_antrian" => $nomor_antrian,  // COMPUTED
        "status" => strtoupper($row['status'] ?? 'MENUNGGU'),
        "created_at" => $row['created_at'],
        "tanggal_servis" => $row['tanggal'],
        "jam_servis" => $row['jam_mulai'],
        "total_biaya" => (int)($row['harga'] ?? 0),
        "nama_kendaraan" => trim(($row['merk'] ?? '') . ' ' . ($row['model'] ?? '') . ' - ' . ($row['nomor_plat'] ?? '')),
        "nama_servis" => $row['nama_servis'] ?? ''
    ];
    $data[] = $item;
}

mysqli_stmt_close($stmt);

logError("list_customer success", ["count" => count($data)]);

jsonResponse("success", "Booking customer", $data);
