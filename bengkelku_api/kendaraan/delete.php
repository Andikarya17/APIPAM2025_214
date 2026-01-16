<?php
require "../config/database.php";
require "../helpers/response.php";

$id = $_POST['id'];

mysqli_query($conn, "DELETE FROM kendaraan WHERE id=$id")
    ? jsonResponse("success", "Kendaraan dihapus")
    : jsonResponse("error", "Gagal hapus kendaraan", null, 400);
