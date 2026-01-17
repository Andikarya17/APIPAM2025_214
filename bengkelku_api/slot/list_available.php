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
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Cast numeric fields to integers for proper JSON parsing
    $row['id'] = (int) $row['id'];
    $row['kapasitas'] = (int) $row['kapasitas'];
    $row['terpakai'] = (int) $row['terpakai'];
    $data[] = $row;
}

jsonResponse("success", "Slot servis tersedia", $data);