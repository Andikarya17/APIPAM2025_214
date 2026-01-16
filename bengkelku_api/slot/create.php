<?php
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
    exit;
}

if (
    empty($_POST['tanggal']) ||
    empty($_POST['jam_mulai']) ||
    empty($_POST['jam_selesai']) ||
    !isset($_POST['kapasitas'])
) {
    jsonResponse("error", "Field tidak lengkap. Wajib: tanggal, jam_mulai, jam_selesai, kapasitas", null, 400);
    exit;
}

$tanggal = $_POST['tanggal'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$kapasitas = intval($_POST['kapasitas']);

if ($kapasitas <= 0) {
    jsonResponse("error", "Kapasitas harus lebih dari 0", null, 400);
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO slot_servis (tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status)
     VALUES (?, ?, ?, ?, 0, 'available')"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
    exit;
}

mysqli_stmt_bind_param($stmt, "sssi", $tanggal, $jam_mulai, $jam_selesai, $kapasitas);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($conn);
    jsonResponse("success", "Slot berhasil dibuat", ["id" => $newId]);
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 400);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);