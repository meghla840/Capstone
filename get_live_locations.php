<?php
include "backend/db.php";

$vehicles = mysqli_query($conn,"SELECT name, driverName, driverPhone, driverLat, driverLng FROM transport_vehicles");
$result = [];
while($v = mysqli_fetch_assoc($vehicles)){
    $result[] = $v;
}
echo json_encode($result);