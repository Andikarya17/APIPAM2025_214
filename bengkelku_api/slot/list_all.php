<?php
require "../config/database.php";
require "../helpers/response.php";

$query = "SELECT id, tanggal, jam_mulai, jam_selesai, kapasitas, terpakai, status 
          FROM slot_servis 
          ORDER BY tanggal DESC, jam_mulai DESC";

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

jsonResponse("success", "Semua slot servis", $data);