<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickaid";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed");
}
?>
`