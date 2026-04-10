<?php
include 'db.php';

$keyword = $_GET['q'];

$query = "SELECT * FROM doctors 
          WHERE name LIKE '%$keyword%' 
          OR specialist LIKE '%$keyword%'";

$result = mysqli_query($conn, $query);
$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);

