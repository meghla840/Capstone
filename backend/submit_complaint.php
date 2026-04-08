<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $complaint = $_POST['complaint'];

    $query = "INSERT INTO complaints (complaint) VALUES ('$complaint')";
    mysqli_query($conn, $query);

    echo "Complaint submitted successfully";
}
