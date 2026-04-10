<?php
include 'db.php';
$h=$_GET['hospital'];

$q="SELECT d.userId,u.name 
FROM doctors d 
JOIN users u ON u.userId=d.userId";

$res=$conn->query($q);

$data=[];
while($row=$res->fetch_assoc()) $data[]=$row;
echo json_encode($data);
?>