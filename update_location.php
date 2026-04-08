<?php
session_start();
include "backend/db.php";

if(!isset($_SESSION['user_id'])){
    exit(json_encode(['status'=>'error','msg'=>'Not logged in']));
}

$userId = $_SESSION['user_id'];
$vehicleId = $_POST['vehicleId'] ?? 0;
$lat = $_POST['lat'] ?? '';
$lng = $_POST['lng'] ?? '';

if($vehicleId && $lat && $lng){
    $lat = floatval($lat);
    $lng = floatval($lng);
    mysqli_query($conn,"UPDATE transport_vehicles 
        SET driverLat='$lat', driverLng='$lng' 
        WHERE id='$vehicleId' AND transportId='$userId'");
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','msg'=>'Missing data']);
}