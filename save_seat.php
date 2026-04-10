<?php
session_start();
include "backend/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$hospital_id = $_POST['hospital_id']; // numeric hospitals.id
$name = $_POST['patient_name'];
$phone = $_POST['phone'];
$date = $_POST['date'];
$day = $_POST['day'];

/* CHECK AVAILABLE BEDS */
$res = mysqli_query($conn, "SELECT * FROM hospitals WHERE id='$hospital_id'");
$hospital = mysqli_fetch_assoc($res);

if(!$hospital){
    die("Hospital not found");
}

if($hospital['availableBeds'] <= 0){
    $_SESSION['error'] = "No seats available!";
    header("Location: hospital_details.php?id=".$hospital_id);
    exit();
}

/* INSERT BOOKING */
$insert = mysqli_query($conn, "INSERT INTO seat_bookings 
(hospital_id, patient_name, phone, date, day)
VALUES 
('$hospital_id','$name','$phone','$date','$day')");

if(!$insert){
    $_SESSION['error'] = "Booking failed!";
    header("Location: hospital_details.php?id=".$hospital_id);
    exit();
}

/* UPDATE AVAILABLE BEDS */
mysqli_query($conn, "UPDATE hospitals 
SET availableBeds = availableBeds - 1 
WHERE id='$hospital_id'");

$_SESSION['success'] = "Seat booked successfully!";
header("Location: hospital_details.php?id=".$hospital_id);
exit();
?>