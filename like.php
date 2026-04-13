<?php
session_start();
include "backend/db.php";

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

$check = mysqli_query($conn,"SELECT * FROM post_likes WHERE post_id=$post_id AND user_id=$user_id");

if(mysqli_num_rows($check)>0){
    mysqli_query($conn,"DELETE FROM post_likes WHERE post_id=$post_id AND user_id=$user_id");
}else{
    mysqli_query($conn,"INSERT INTO post_likes(post_id,user_id) VALUES($post_id,$user_id)");
}
?>