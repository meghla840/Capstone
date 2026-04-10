<?php
session_start();
include 'db.php';

$id=$_SESSION['user_id'];

$res=$conn->query("SELECT * FROM appointments WHERE patientId='$id'");
$data=[];
while($row=$res->fetch_assoc()) $data[]=$row;

echo json_encode($data);
?>