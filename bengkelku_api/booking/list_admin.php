<?php
/**
 * BOOKING LIST ADMIN - Based on ACTUAL DB schema
 * 
 * Tables used:
 * - booking: id, user_id, kendaraan_id, jenis_servis_id, slot_servis_id, nomot_antrian, status, created_at
 * - users: id, nama, username, password, role, created_at
 * - kendaraan: id, user_id, merk, model, nomor_plat, tahun, warna, created_at
 * - jenis_servis: id, nama_servis, harga, deskripsi, is_active
 * - slot_servis: id, tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

logError("booking/list_admin called", []);

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
        u.nama as nama_pengguna,
        u.username,
        k.merk,
        k.model,
        k.nomor_plat,
        k.tahun,
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
        // User info
        "nama_pengguna" => $row['nama_pengguna'] ?? '',
        "username" => $row['username'] ?? '',
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

logError("list_admin success", ["count" => count($data)]);

jsonResponse("success", "Semua booking", $data);
