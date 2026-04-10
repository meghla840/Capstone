<?php
include "backend/db.php";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $name = $_POST['name'];
    $address = $_POST['address'];
    $vehicle = $_POST['vehicle'];
    $driver_phone = $_POST['driver_phone'];
    $user_phone = $_POST['phone']; // ✅ এখানে phone নাও

    // নিরাপত্তার জন্য (optional but recommended)
    $name = mysqli_real_escape_string($conn, $name);
    $address = mysqli_real_escape_string($conn, $address);
    $vehicle = mysqli_real_escape_string($conn, $vehicle);
    $driver_phone = mysqli_real_escape_string($conn, $driver_phone);
    $user_phone = mysqli_real_escape_string($conn, $user_phone);

    $query = "INSERT INTO book_ambulance 
    (name, address, vehicle_name, driver_phone, user_phone, created_at) 
    VALUES 
    ('$name', '$address', '$vehicle', '$driver_phone', '$user_phone', NOW())";

    if(mysqli_query($conn, $query)){
        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }
}
?>