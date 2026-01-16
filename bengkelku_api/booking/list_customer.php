<?php
require "../config/database.php";
require "../helpers/response.php";

if (empty($_GET['user_id'])) {
    jsonResponse("error", "user_id wajib diisi", null, 400);
    exit;
}

$user_id = intval($_GET['user_id']);

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
        CONCAT(k.merk, ' ', k.model, ' - ', k.nomor_plat) as nama_kendaraan,
        s.nama_servis
    FROM booking b
    LEFT JOIN kendaraan k ON b.kendaraan_id = k.id
    LEFT JOIN servis s ON b.servis_id = s.id
    WHERE b.pengguna_id = ?
    ORDER BY b.tanggal_servis DESC, b.jam_servis DESC
";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

jsonResponse("success", "Booking customer", $data);

mysqli_stmt_close($stmt);
mysqli_close($conn);