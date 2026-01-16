<?php
require "../config/database.php";
require "../helpers/response.php";

$user_id = $_GET['user_id'];

$q = mysqli_query($conn,
    "SELECT * FROM booking WHERE user_id=$user_id ORDER BY created_at DESC"
);

$data=[];
while($r=mysqli_fetch_assoc($q)) $data[]=$r;

jsonResponse("success", "Riwayat booking", $data);
