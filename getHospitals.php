<?php
include 'db.php';
$res = $conn->query("SELECT userId, hospitalName FROM hospitals");
$data=[];
while($row=$res->fetch_assoc()) $data[]=$row;
echo json_encode($data);
?>