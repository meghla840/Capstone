<?php
session_start();

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

include "backend/db.php";

/* ================= Check Login ================= */
if(!isset($_SESSION['user_id'])){
    echo json_encode([
        "status" => "error",
        "message" => "Login required"
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= Fetch User Address ================= */
$stmtUser = $conn->prepare("SELECT address FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();

$userRes = $stmtUser->get_result();
$userData = $userRes->fetch_assoc();

$stmtUser->close();

$location = $userData['address'] ?? 'Unknown';

/* ================= Save Alert ================= */
$stmt = $conn->prepare("
    INSERT INTO traffic_alerts (user_id, location, created_at, status) 
    VALUES (?, ?, NOW(), 'pending')
");

$stmt->bind_param("is", $user_id, $location);

/* ================= Execute ================= */
if($stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "Alert sent successfully",
        "location" => $location
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to send alert"
    ]);
}

$stmt->close();

/* IMPORTANT: stop any extra output */
exit();