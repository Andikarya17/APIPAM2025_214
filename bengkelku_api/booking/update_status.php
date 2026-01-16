<?php
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
    exit;
}

if (empty($_POST['id']) || empty($_POST['status'])) {
    jsonResponse("error", "Field tidak lengkap. Wajib: id, status", null, 400);
    exit;
}

$id = intval($_POST['id']);
$status = $_POST['status'];

$allowedStatus = ['MENUNGGU', 'DIPROSES', 'SELESAI', 'DIBATALKAN'];
if (!in_array(strtoupper($status), $allowedStatus)) {
    jsonResponse("error", "Status tidak valid. Pilihan: MENUNGGU, DIPROSES, SELESAI, DIBATALKAN", null, 400);
    exit;
}

$status = strtoupper($status);

$stmt = mysqli_prepare($conn, "UPDATE booking SET status = ? WHERE id = ?");

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        jsonResponse("success", "Status booking berhasil diperbarui", null);
    } else {
        jsonResponse("error", "Booking tidak ditemukan", null, 404);
    }
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 400);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);