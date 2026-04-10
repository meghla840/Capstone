<?php
include 'db.php';

$id=$_POST['id'];
$status=$_POST['status'];

$conn->query("UPDATE appointments SET status='$status' WHERE id='$id'");
?>