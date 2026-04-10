<?php
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$userId = $data['userId'];

$sql = "UPDATE users SET
name='{$data['name']}',
phone='{$data['phone']}',
bloodGroup='{$data['bloodGroup']}',
healthIssues='{$data['healthIssues']}',
address='{$data['address']}',
specialization='{$data['specialization']}',
pharmacyName='{$data['pharmacyName']}',
driverName='{$data['driverName']}',
vehicleType='{$data['vehicleType']}',
numberPlate='{$data['numberPlate']}',
drivingLicense='{$data['drivingLicense']}'
WHERE userId='$userId'";

if(mysqli_query($conn,$sql)){
  echo json_encode(["status"=>"success"]);
}else{
  echo json_encode(["status"=>"error"]);
}
?>