<?php
require "../config/database.php";
require "../helpers/response.php";

$query = "SELECT id, nama_servis, harga, deskripsi, is_active 
          FROM jenis_servis 
          ORDER BY nama_servis ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    jsonResponse("error", "Query failed: " . mysqli_error($conn), null, 500);
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Cast numeric fields to integers for proper JSON parsing
    $row['id'] = (int) $row['id'];
    $row['harga'] = (int) $row['harga'];
    $row['is_active'] = (bool) $row['is_active'];
    $data[] = $row;
}

jsonResponse("success", "Semua servis", $data);