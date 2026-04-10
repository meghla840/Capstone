<?php
include "backend/db.php";

$id=$_POST['reviewId'];
$name=$_POST['userName'];
$comment=$_POST['comment'];

$conn->query("INSERT INTO review_replies (reviewId,userName,comment) 
VALUES ('$id','$name','$comment')");