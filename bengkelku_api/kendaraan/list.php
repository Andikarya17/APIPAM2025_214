<?php
require "../config/database.php";
require "../helpers/response.php";

$user_id = $_GET['user_id'];

$q = mysqli_query($conn,
    "SELECT * FROM kendaraan WHERE user_id = $user_id"
);

$data = [];
while ($row = mysqli_fetch_assoc($q)) {
    $data[] = $row;
}

jsonResponse("success", "Daftar kendaraan", $data);
