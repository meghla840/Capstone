<?php
include "backend/db.php";
$id=$_GET['id'];
$comment=$_GET['comment'];
$conn->query("UPDATE pharmacy_reviews SET comment='$comment' WHERE id='$id'");