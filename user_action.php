<?php
include "backend/db.php";

if(isset($_POST['action']) && isset($_POST['id'])){

    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if($action == 'block'){
        mysqli_query($conn, "UPDATE users SET status='blocked' WHERE id=$id");
        echo "blocked";
    }

    if($action == 'unblock'){
        mysqli_query($conn, "UPDATE users SET status='active' WHERE id=$id");
        echo "unblocked";
    }
}
?>