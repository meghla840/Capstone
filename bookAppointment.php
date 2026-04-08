<?php
session_start();
include 'backend/db.php';

/* ================= CHECK LOGIN ================= */
if(!isset($_SESSION['user_id'])){
    die("Please login first");
}

/* ================= GET DATA ================= */
$doctorId  = $_POST['doctor'] ?? null;
$patientName = $_POST['patient_name'] ?? null;
$phone     = $_POST['phone'] ?? null;
$date      = $_POST['date'] ?? null;
$problem   = $_POST['problem'] ?? null;
$hospitalId = $_POST['hospital'] ?? null; // optional

/* ================= VALIDATION ================= */
if(!$doctorId || !$patientName || !$phone || !$date){
    die("Missing required fields");
}

/* ================= DEFAULT VALUES ================= */
$status = "pending";
$slotId = null;
$fee = null;

/* ================= INSERT ================= */
$stmt = $conn->prepare("
INSERT INTO appointments 
(doctorId, patientName, phone, slotId, fee, hospitalUserId, status, problem, appointment_date) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssssss",
    $doctorId,
    $patientName,
    $phone,
    $slotId,
    $fee,
    $hospitalId,
    $status,
    $problem,
    $date
);

/* ================= EXECUTE ================= */
if($stmt->execute()){
    header("Location: doctor_details.php?id=$doctorId&success=1");
    exit();
} else {
    echo "error: " . $stmt->error;
}
?>