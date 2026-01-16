<?php
require "../config/database.php";
require "../helpers/response.php";

/*
  Slot dianggap AVAILABLE jika:
  1. status = 'available'
  2. kapasitas_terpakai < kapasitas
  3. tanggal >= hari ini
*/

$query = "
    SELECT
        id,
        tanggal,
        jam,
        kapasitas,
        kapasitas_terpakai
    FROM slot_servis
    WHERE status = 'available'
      AND kapasitas_terpakai < kapasitas
      AND tanggal >= CURDATE()
    ORDER BY tanggal ASC, jam ASC
";

$result = mysqli_query($conn, $query);

$slots = [];
while ($row = mysqli_fetch_assoc($result)) {
    $slots[] = $row;
}

jsonResponse(
    "success",
    "Slot servis tersedia",
    $slots
);
