<?php
include "backend/db.php";
$id=$_GET['id'];
$conn->query("UPDATE pharmacy_reviews SET likes=likes+1 WHERE id='$id'");