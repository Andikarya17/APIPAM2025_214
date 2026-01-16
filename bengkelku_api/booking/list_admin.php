<?php
require "../config/database.php";
require "../helpers/response.php";

$q = mysqli_query($conn,
    "SELECT * FROM booking ORDER BY created_at DESC"
);

$data=[];
while($r=mysqli_fetch_assoc($q)) $data[]=$r;

jsonResponse("success", "Semua booking", $data);
