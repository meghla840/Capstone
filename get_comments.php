<?php
include "backend/db.php";

$post_id = $_GET['post_id'];

$res = mysqli_query($conn,"SELECT c.*,u.name 
FROM post_comments c
JOIN users u ON c.user_id=u.id
WHERE post_id=$post_id
ORDER BY c.created_at DESC");

while($r = mysqli_fetch_assoc($res)){
echo "<p><b>{$r['name']}:</b> {$r['comment']}</p>";
}
?>