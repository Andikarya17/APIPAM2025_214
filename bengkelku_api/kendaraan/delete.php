<?php
require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

if (empty($_POST['id'])) {
    jsonResponse("error", "ID kendaraan tidak valid", null, 400);
}

$id = intval($_POST['id']);

if ($id <= 0) {
    jsonResponse("error", "ID harus berupa angka positif", null, 400);
}

$stmt = mysqli_prepare($conn, "DELETE FROM kendaraan WHERE id = ?");

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        jsonResponse("success", "Kendaraan berhasil dihapus", null);
    } else {
        jsonResponse("error", "Kendaraan tidak ditemukan", null, 404);
    }
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}