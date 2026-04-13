<?php
session_start();
include "backend/db.php";

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];
$comment = $_POST['comment'];

mysqli_query($conn,"INSERT INTO post_comments(post_id,user_id,comment)
VALUES('$post_id','$user_id','$comment')");
?>