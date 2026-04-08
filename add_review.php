<?php
include "backend/db.php";

$pharmacyId = $_POST['pharmacyId'];
$userName = $_POST['userName'];
$comment = $_POST['comment'];
$rating = $_POST['rating'];

if(!$pharmacyId){
    echo json_encode(["status"=>"error"]);
    exit;
}

mysqli_query($conn,"INSERT INTO pharmacy_reviews 
(pharmacyId,userName,comment,rating,created_at) 
VALUES ('$pharmacyId','$userName','$comment','$rating',NOW())");

echo json_encode(["status"=>"success"]);