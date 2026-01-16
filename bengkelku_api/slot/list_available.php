<?php
require "../config/database.php";
require "../helpers/response.php";

$query = "
    SELECT id, tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status
    FROM slot_servis
    WHERE terpakai < kapasitas
      AND tanggal >= CURDATE()
      AND status = 'available'
    ORDER BY tanggal ASC, jam_mulai ASC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    jsonResponse("error", "Query failed: " . mysqli_error($conn), null, 500);
    exit;
}

$slots = [];
while ($row = mysqli_fetch_assoc($result)) {
    $slots[] = $row;
}

jsonResponse("success", "Slot servis tersedia", $slots);

mysqli_close($conn);