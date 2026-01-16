<?php
require "../config/database.php";
require "../helpers/response.php";

if (empty($_GET['user_id'])) {
    jsonResponse("error", "user_id wajib diisi", null, 400);
    exit;
}

$user_id = intval($_GET['user_id']);

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, pengguna_id, merk, model, nomor_plat, tahun 
     FROM kendaraan 
     WHERE pengguna_id = ?
     ORDER BY id DESC"
);

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

jsonResponse("success", "Kendaraan pengguna", $data);

mysqli_stmt_close($stmt);
mysqli_close($conn);