<?php
session_start();
include "db.php";

if($_SERVER['REQUEST_METHOD']=="GET"){

    $res = mysqli_query($conn,"SELECT * FROM hospitals");
    $data = [];

    while($row = mysqli_fetch_assoc($res)){
        $data[] = $row;
    }

    echo json_encode($data);
}

// BOOK
if($_SERVER['REQUEST_METHOD']=="POST"){

    $input = json_decode(file_get_contents("php://input"), true);

    $hospitalId = $input['hospitalId'];

    $uid = $_SESSION['user_id'];

    $userQ = mysqli_query($conn,"SELECT userId FROM users WHERE id='$uid'");
    $user = mysqli_fetch_assoc($userQ);

    $appointment = json_encode([
        "hospitalId"=>$hospitalId,
        "date"=>date("Y-m-d"),
        "status"=>"pending"
    ]);

    mysqli_query($conn,"UPDATE patients SET 
    appointments = JSON_ARRAY_APPEND(appointments,'$', '$appointment')
    WHERE userId='{$user['userId']}'");

    echo "ok";
}