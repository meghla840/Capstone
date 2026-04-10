<?php
include 'db.php';

$query = "SELECT * FROM doctors ORDER BY rating DESC LIMIT 8";
$result = mysqli_query($conn, $query);

$doctors = [];

while ($row = mysqli_fetch_assoc($result)) {
    $doctors[] = $row;
}

header('Content-Type: application/json');
echo json_encode($doctors);
