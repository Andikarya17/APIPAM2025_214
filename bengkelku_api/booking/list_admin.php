<?php
require "../config/database.php";
require "../helpers/response.php";

$query = "
    SELECT 
        b.id,
        b.pengguna_id,
        b.kendaraan_id,
        b.servis_id,
        b.slot_servis_id,
        b.tanggal_servis,
        b.jam_servis,
        b.status,
        b.total_biaya,
        b.nomor_antrian,
        p.nama as nama_pengguna,
        CONCAT(k.merk, ' ', k.model, ' - ', k.nomor_plat) as nama_kendaraan,
        s.nama_servis
    FROM booking b
    LEFT JOIN pengguna p ON b.pengguna_id = p.id
    LEFT JOIN kendaraan k ON b.kendaraan_id = k.id
    LEFT JOIN servis s ON b.servis_id = s.id
    ORDER BY b.tanggal_servis DESC, b.jam_servis DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    jsonResponse("error", "Query failed: " . mysqli_error($conn), null, 500);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

jsonResponse("success", "Semua booking", $data);

mysqli_close($conn);