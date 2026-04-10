<?php
session_start();
include "backend/db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient'){
    header("Location: login.php");
    exit();
}

if(isset($_POST['reportId'])){
    $reportId = $_POST['reportId'];

    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT filePath FROM patient_reports WHERE id='$reportId'"));
    if($res && $res['filePath'] && file_exists($res['filePath'])){
        unlink($res['filePath']); // delete file from server
    }

    mysqli_query($conn, "DELETE FROM patient_reports WHERE id='$reportId'");

    header("Location: patient_dashboard.php");
    exit;
}
?>