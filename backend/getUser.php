<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode(["error"=>"Not logged in"]);
    exit;
}

$userId = $_SESSION['user_id'];

// get base user
$userQ = mysqli_query($conn,"SELECT * FROM users WHERE id='$userId'");
$user = mysqli_fetch_assoc($userQ);

$role = $user['role'];

// role ভিত্তিক extra data
if($role == "patient"){
    $extraQ = mysqli_query($conn,"SELECT * FROM patients WHERE userId='{$user['userId']}'");
}
elseif($role == "doctor"){
    $extraQ = mysqli_query($conn,"SELECT * FROM doctors WHERE userId='{$user['userId']}'");
}
elseif($role == "hospital"){
    $extraQ = mysqli_query($conn,"SELECT * FROM hospitals WHERE userId='{$user['userId']}'");
}
elseif($role == "pharmacy"){
    $extraQ = mysqli_query($conn,"SELECT * FROM pharmacies WHERE userId='{$user['userId']}'");
}
elseif($role == "transport"){
    $extraQ = mysqli_query($conn,"SELECT * FROM transports WHERE userId='{$user['userId']}'");
}

$extra = mysqli_fetch_assoc($extraQ);

echo json_encode([
    "user"=>$user,
    "extra"=>$extra
]);