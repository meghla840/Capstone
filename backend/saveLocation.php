<?php
session_start();
include "db.php";

$id = $_SESSION['user_id'];

$lat = $_POST['lat'];
$lon = $_POST['lon'];

mysqli_query($conn,"UPDATE users SET latitude='$lat', longitude='$lon' WHERE id='$id'");


