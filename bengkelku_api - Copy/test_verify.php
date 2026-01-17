<?php
require "config/database.php"; // pastikan path BENAR

$username = "admin";
$password = "admin";

$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

var_dump($user);
var_dump(password_verify($password, $user['password']));
