<?php
include 'db.php';

$id = $_GET['id'];

$query = "SELECT * FROM doctors WHERE id=$id";
$result = mysqli_query($conn, $query);
$doctor = mysqli_fetch_assoc($result);

echo json_encode($doctor);
