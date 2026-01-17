<?php
/**
 * SLOT DELETE - Proper implementation with prepared statements
 */
ob_start();

require_once "../config/database.php";
require_once "../helpers/response.php";

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse("error", "Method not allowed", null, 405);
}

// Get input
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate
if ($id <= 0) {
    jsonResponse("error", "ID tidak valid", null, 400);
}

// Delete with prepared statement
$stmt = mysqli_prepare($conn, "DELETE FROM slot_servis WHERE id = ?");
if (!$stmt) {
    logError("slot/delete: prepare failed", ["error" => mysqli_error($conn)]);
    jsonResponse("error", "Database error", null, 500);
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (!mysqli_stmt_execute($stmt)) {
    logError("slot/delete: execute failed", ["error" => mysqli_stmt_error($stmt)]);
    jsonResponse("error", "Database error", null, 500);
}

$affected = mysqli_affected_rows($conn);
mysqli_stmt_close($stmt);

if ($affected > 0) {
    jsonResponse("success", "Slot berhasil dihapus", null);
} else {
    jsonResponse("error", "Slot tidak ditemukan", null, 404);
}
