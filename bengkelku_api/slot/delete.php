<?php
require "../config/database.php";
require "../helpers/response.php";

$id = $_POST['id'];

if (!$id) {
    jsonResponse("error", "ID slot tidak valid", null, 400);
}

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM slot_servis WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    jsonResponse("success", "Slot servis dihapus");
}

jsonResponse("error", "Gagal menghapus slot", null, 400);
