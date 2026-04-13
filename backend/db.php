<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "quickaid";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("DB ERROR: " . mysqli_connect_error());
}


?>