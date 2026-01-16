<?php
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
    exit;
}

if (
    empty($_POST['id']) ||
    empty($_POST['tanggal']) ||
    empty($_POST['jam_mulai']) ||
    empty($_POST['jam_selesai']) ||
    !isset($_POST['kapasitas'])
) {
    jsonResponse("error", "Field tidak lengkap. Wajib: id, tanggal, jam_mulai, jam_selesai, kapasitas", null, 400);
    exit;
}

$id = intval($_POST['id']);
$tanggal = $_POST['tanggal'];
$jam_mulai = $_POST['jam_mulai'];
$jam_selesai = $_POST['jam_selesai'];
$kapasitas = intval($_POST['kapasitas']);

if ($id <= 0) {
    jsonResponse("error", "ID tidak valid", null, 400);
    exit;
}

if ($kapasitas <= 0) {
    jsonResponse("error", "Kapasitas harus lebih dari 0", null, 400);
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE slot_servis 
     SET tanggal = ?, jam_mulai = ?, jam_selesai = ?, kapasitas = ?
     WHERE id = ?"
);

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
    exit;
}

mysqli_stmt_bind_param($stmt, "sssii", $tanggal, $jam_mulai, $jam_selesai, $kapasitas, $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        jsonResponse("success", "Slot berhasil diperbarui", null);
    } else {
        jsonResponse("error", "Slot tidak ditemukan", null, 404);
    }
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 400);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);