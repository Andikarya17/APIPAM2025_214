<?php
require "../config/database.php";
require "../helpers/response.php";

$q = mysqli_query($conn,
    "SELECT * FROM jenis_servis WHERE is_active = 1"
);

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = $r;
}

jsonResponse("success", "Jenis servis", $data);
