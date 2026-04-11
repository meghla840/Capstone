<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "quickaid");

if ($conn->connect_error) {
    die(json_encode([]));
}

$type = $_GET['type'] ?? '';

// ✅ CORRECT JOIN (userId with userId)
$sql = "SELECT 
            d.*, 
            u.name AS doctorName
        FROM doctors d
        JOIN users u ON d.userId = u.userId
        WHERE d.specialization LIKE '%$type%'";

$result = $conn->query($sql);

$doctors = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = [
            "name" => $row['doctorName'],
            "specialization" => $row['specialization'],
            "clinic" => $row['clinic'],
            "bmdc" => $row['bmdc'],
            "fees" => $row['consultationFees']
        ];
    }
}

echo json_encode($doctors);
$conn->close();
?>