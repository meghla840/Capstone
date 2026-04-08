<?php
include "backend/db.php";

header("Content-Type: application/json");

$query = "SELECT * FROM pharmacies";
$result = mysqli_query($conn, $query);

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>