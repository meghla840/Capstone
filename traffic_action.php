<?php
include "backend/db.php";

if(isset($_POST['action']) && $_POST['action'] == 'approve'){

    $id = $_POST['id'];

    // update status
    mysqli_query($conn, "UPDATE traffic_alerts SET status='approved' WHERE id='$id'");

    // OPTIONAL: create high priority alert (separate table)
    $alert = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM traffic_alerts WHERE id='$id'"));

    mysqli_query($conn, "
        INSERT INTO high_priority_alerts (user_id, location, message, created_at)
        VALUES (
            '{$alert['user_id']}',
            '{$alert['location']}',
            '{$alert['message']}',
            NOW()
        )
    ");

    echo "success";
}