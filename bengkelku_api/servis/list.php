<?php
require "../config/database.php";
require "../helpers/response.php";

$query = "SELECT id, nama_servis, harga, deskripsi, aktif 
          FROM servis 
          ORDER BY nama_servis ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    jsonResponse("error", "Query failed: " . mysqli_error($conn), null, 500);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['aktif'] = (bool) $row['aktif'];
    $data[] = $row;
}

jsonResponse("success", "Semua servis", $data);

mysqli_close($conn);