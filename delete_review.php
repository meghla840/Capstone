<?php
include "backend/db.php";
$id=$_GET['id'];
$conn->query("DELETE FROM pharmacy_reviews WHERE id='$id'");