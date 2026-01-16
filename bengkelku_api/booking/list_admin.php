<?php
// Start output buffering to catch any stray output
ob_start();

require "../config/database.php";
require "../helpers/response.php";

$query = "
    SELECT 
        b.id,
        b.user_id,
        b.kendaraan_id,
        b.jenis_servis_id,
        b.slot_servis_id,
        b.tanggal_servis,
        b.jam_servis,
        b.nomor_antrian,
        b.status,
        b.total_biaya,
        u.nama as nama_pengguna,
        CONCAT(k.merk, ' ', k.model, ' - ', k.nomor_plat) as nama_kendaraan,
        j.nama_servis
    FROM booking b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN kendaraan k ON b.kendaraan_id = k.id
    LEFT JOIN jenis_servis j ON b.jenis_servis_id = j.id
    ORDER BY b.tanggal_servis DESC, b.jam_servis DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    jsonResponse("error", "Query failed: " . mysqli_error($conn), null, 500);
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Cast numeric fields to integers for proper JSON parsing
    $row['id'] = (int) $row['id'];
    $row['user_id'] = (int) $row['user_id'];
    $row['kendaraan_id'] = (int) $row['kendaraan_id'];
    $row['jenis_servis_id'] = (int) $row['jenis_servis_id'];
    $row['slot_servis_id'] = (int) $row['slot_servis_id'];
    $row['nomor_antrian'] = (int) $row['nomor_antrian'];
    $row['total_biaya'] = (int) $row['total_biaya'];
    // Uppercase status for Android compatibility
    $row['status'] = strtoupper($row['status']);
    $data[] = $row;
}

jsonResponse("success", "Semua booking", $data);