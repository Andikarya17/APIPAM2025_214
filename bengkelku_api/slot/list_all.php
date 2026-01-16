<?php
require "../config/database.php";
require "../helpers/response.php";

$q = mysqli_query(
    $conn,
    "SELECT * FROM slot_servis ORDER BY tanggal DESC, jam DESC"
);

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = $r;
}

jsonResponse("success", "Semua slot servis", $data);
