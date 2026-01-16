<?php
require "../config/database.php";
require "../helpers/response.php";

$query = "SELECT id, tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status 
          FROM slot_servis 
          ORDER BY tanggal DESC, jam_mulai DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    jsonResponse("error", "Query failed: " . mysqli_error($conn), null, 500);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

jsonResponse("success", "Semua slot servis", $data);

mysqli_close($conn);