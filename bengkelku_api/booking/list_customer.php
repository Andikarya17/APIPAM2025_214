<?php
/**
 * BOOKING LIST CUSTOMER - Based on ACTUAL DB schema
 * 
 * Tables used:
 * - booking: id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, nomot_antrian, status, created_at
 * - kendaraan: id, user_id, merk, model, nomor_plat, tahun, warna, created_at
 * - jenis_servis: id, nama_servis, harga, deskripsi, is_active
 * - slot_servis: id, tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Get and validate user_id
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

logError("booking/list_customer called", ["user_id" => $user_id]);

if ($user_id <= 0) {
    jsonResponse("error", "user_id wajib diisi dan > 0", null, 400);
}

// Query with JOINs - using ACTUAL column names from schema
$query = "
    SELECT 
        b.id,
        b.user_id,
        b.kendaraan_id,
        b.jenis_servis_id,
        b.slot_servis_id,
        b.nomot_antrian,
        b.status,
        b.created_at,
        k.merk,
        k.model,
        k.nomor_plat,
        k.tahun,
        k.warna,
        j.nama_servis,
        j.harga,
        j.deskripsi,
        j.is_active,
        s.tanggal,
        s.jam_mulai,
        s.jam_selesai,
        s.kapasitas,
        s.terpakai,
        s.status as slot_status
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
    // Build response matching Android model
    $item = [
        "id" => (int)$row['id'],
        "user_id" => (int)$row['user_id'],
        "kendaraan_id" => (int)$row['kendaraan_id'],
        "jenis_servis_id" => (int)$row['jenis_servis_id'],
        "slot_servis_id" => (int)$row['slot_servis_id'],
        "nomor_antrian" => (int)$row['nomot_antrian'],  // Map nomot_antrian -> nomor_antrian
        "status" => strtoupper($row['status'] ?? 'MENUNGGU'),
        "created_at" => $row['created_at'],
        // Computed fields for Android
        "tanggal_servis" => $row['tanggal'],
        "jam_servis" => $row['jam_mulai'],
        "total_biaya" => (int)($row['harga'] ?? 0),
        // Kendaraan info
        "nama_kendaraan" => trim(($row['merk'] ?? '') . ' ' . ($row['model'] ?? '') . ' - ' . ($row['nomor_plat'] ?? '')),
        // Servis info
        "nama_servis" => $row['nama_servis'] ?? '',
        "harga" => (int)($row['harga'] ?? 0),
        "deskripsi" => $row['deskripsi'],
        "is_active" => (bool)($row['is_active'] ?? false),
        // Slot info
        "tanggal" => $row['tanggal'],
        "jam_mulai" => $row['jam_mulai'],
        "jam_selesai" => $row['jam_selesai']
    ];
    $data[] = $item;
}

mysqli_stmt_close($stmt);

logError("list_customer success", ["count" => count($data)]);

jsonResponse("success", "Booking customer", $data);
