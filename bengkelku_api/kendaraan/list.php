<?php
ob_start();
require "../config/database.php";
require "../helpers/response.php";

if (empty($_GET['user_id'])) {
    jsonResponse("error", "user_id wajib diisi", null, 400);
}

$user_id = intval($_GET['user_id']);

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, user_id, merk, model, nomor_plat, tahun, warna 
     FROM kendaraan 
     WHERE user_id = ?
     ORDER BY id DESC"
);

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
    if ($row['tahun'] !== null) {
        $row['tahun'] = (int) $row['tahun'];
    }
    $data[] = $row;
}

jsonResponse("success", "Kendaraan pengguna", $data);