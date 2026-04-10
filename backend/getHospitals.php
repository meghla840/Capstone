<?php
include "db.php";

$name = $_GET['name'] ?? '';
$division = $_GET['division'] ?? '';
$district = $_GET['district'] ?? '';

$sql = "SELECT 
            hospitals.*, 
            users.name AS hospitalUserName,
            users.profilePic
        FROM hospitals
        JOIN users ON hospitals.userId = users.userId
        WHERE users.role = 'hospital'";

if($name != ''){
    $sql .= " AND hospitals.hospitalName LIKE '%$name%'";
}
if($division != ''){
    $sql .= " AND hospitals.division='$division'";
}
if($district != ''){
    $sql .= " AND hospitals.district='$district'";
}

$result = mysqli_query($conn, $sql);

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>