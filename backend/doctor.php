<?php
session_start();
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$userId = $_SESSION['user_id'];

$userQ = mysqli_query($conn,"SELECT userId FROM users WHERE id='$userId'");
$user = mysqli_fetch_assoc($userQ);

mysqli_query($conn,"UPDATE doctors SET 
availableDays='{$data['days']}',
availableTimes='{$data['time']}',
consultationFees='{$data['fees']}'
WHERE userId='{$user['userId']}'");

echo "ok";