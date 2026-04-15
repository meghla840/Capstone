<?php
header("Content-Type: application/json");
include "backend/db.php";

$type = $_GET['type'] ?? '';

$stmt = $conn->prepare("
    SELECT d.*, u.name AS doctorName
    FROM doctors d
    JOIN users u ON d.userId = u.userId
    WHERE d.specialization LIKE ?
");

$search = "%$type%";
$stmt->bind_param("s", $search);

$stmt->execute();
$result = $stmt->get_result();

$doctors = [];

while ($row = $result->fetch_assoc()) {
$doctors[] = [
    "name" => $row['doctorName'],
    "specialization" => $row['specialization'],
    "clinic" => $row['clinic'],
    "bmdc" => $row['bmdc'],
    "fees" => $row['consultationFees'],
    "experience" => $row['experienceYears'] // ✅ ADD THIS
];
}

// ⭐ SORT BY EXPERIENCE (BEST FIRST)
usort($doctors, function($a, $b) {

    // 1. Experience priority
    if ($b['experience'] != $a['experience']) {
        return $b['experience'] - $a['experience'];
    }

    // 2. If same experience → lower fees is better
    return $a['fees'] - $b['fees'];
});


echo json_encode([
    "allDoctors" => $doctors,
    "bestDoctor" => $doctors[0] ?? null
]);