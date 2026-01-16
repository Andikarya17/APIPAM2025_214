<?php
// Start output buffering to catch any stray output
ob_start();

require "../config/database.php";
require "../helpers/response.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

if (empty($_POST['id']) || empty($_POST['status'])) {
    jsonResponse("error", "Field tidak lengkap. Wajib: id, status", null, 400);
}

$id = intval($_POST['id']);
$rawStatus = strtolower(trim($_POST['status']));

// Normalize status - Android sends MENUNGGU/DIPROSES/SELESAI (uppercase)
// Map all valid variants and store as uppercase for consistency
$statusMap = [
    'menunggu' => 'MENUNGGU',
    'diproses' => 'DIPROSES',
    'dalam_proses' => 'DIPROSES',  // Legacy fallback
    'selesai' => 'SELESAI',
    'dibatalkan' => 'DIBATALKAN'
];

if (!array_key_exists($rawStatus, $statusMap)) {
    jsonResponse("error", "Status tidak valid. Pilihan: MENUNGGU, DIPROSES, SELESAI, DIBATALKAN", null, 400);
}

$status = $statusMap[$rawStatus];

$stmt = mysqli_prepare($conn, "UPDATE booking SET status = ? WHERE id = ?");

if (!$stmt) {
    jsonResponse("error", "Prepare failed: " . mysqli_error($conn), null, 500);
}

mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        jsonResponse("success", "Status booking berhasil diperbarui", null);
    } else {
        jsonResponse("error", "Booking tidak ditemukan", null, 404);
    }
} else {
    jsonResponse("error", "SQL Error: " . mysqli_stmt_error($stmt), null, 500);
}