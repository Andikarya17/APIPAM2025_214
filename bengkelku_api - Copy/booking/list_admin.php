<?php
/**
 * BOOKING LIST ADMIN - NO antrian column in database
 * 
 * Actual booking table columns:
 * - id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, status, created_at
 * 
 * nomor_antrian is COMPUTED using subquery
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

logError("booking/list_admin called", []);

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
        u.nama as nama_pengguna,
        k.merk,
        k.model,
        k.nomor_plat,
        j.nama_servis,
        j.harga,
        s.tanggal,
        s.jam_mulai,
        s.jam_selesai
    FROM booking b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN kendaraan k ON b.kendaraan_id = k.id
    LEFT JOIN jenis_servis j ON b.jenis_servis_id = j.id
    LEFT JOIN slot_servis s ON b.slot_servis_id = s.id
    ORDER BY b.created_at DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    logError("list_admin query failed", ["error" => mysqli_error($conn)]);
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
        "nama_pengguna" => $row['nama_pengguna'] ?? '',
        "nama_kendaraan" => trim(($row['merk'] ?? '') . ' ' . ($row['model'] ?? '') . ' - ' . ($row['nomor_plat'] ?? '')),
        "nama_servis" => $row['nama_servis'] ?? ''
    ];
    $data[] = $item;
}

logError("list_admin success", ["count" => count($data)]);

jsonResponse("success", "Semua booking", $data);
