<?php
require "../config/database.php";
require "../helpers/response.php";

if (empty($_GET['user_id'])) {
    jsonResponse("error", "user_id wajib diisi", null, 400);
}

$user_id = intval($_GET['user_id']);

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
        CONCAT(k.merk, ' ', k.model, ' - ', k.nomor_plat) as nama_kendaraan,
        j.nama_servis
    FROM booking b
    LEFT JOIN kendaraan k ON b.kendaraan_id = k.id
    LEFT JOIN jenis_servis j ON b.jenis_servis_id = j.id
    WHERE b.user_id = ?
    ORDER BY b.tanggal_servis DESC, b.jam_servis DESC
";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

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

jsonResponse("success", "Booking customer", $data);