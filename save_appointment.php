<?php
session_start();
include "backend/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$patient_name = $_POST['patient_name'];
$phone = $_POST['phone'];
$doctor_id = $_POST['doctor_id'];
$hospital_id = $_POST['hospital_id'];
$date = $_POST['date'];
$problem = $_POST['problem'];

$query = "INSERT INTO appointments 
(doctorId, patientName, phone, hospitalUserId, problem, appointment_date)
VALUES 
('$doctor_id', '$patient_name', '$phone', '$hospital_id', '$problem', '$date')";

if(mysqli_query($conn, $query)){
    $_SESSION['success'] = "Appointment booked successfully!";
}else{
    $_SESSION['error'] = "Something went wrong!";
}

header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>