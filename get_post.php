<?php
include "backend/db.php";

$id = $_GET['id'];

/* POST */
$post = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT fp.*, u.name, u.role, u.profilePic
FROM forum_posts fp
JOIN users u ON fp.user_id=u.id
WHERE fp.id=$id
"));

/* COMMENTS */
$comments = [];
$res = mysqli_query($conn,"
SELECT c.*, u.name, u.profilePic
FROM post_comments c
JOIN users u ON c.user_id=u.id
WHERE c.post_id=$id
ORDER BY c.created_at DESC
");

while($r = mysqli_fetch_assoc($res)){
    $comments[] = $r;
}

echo json_encode([
    "post"=>$post,
    "comments"=>$comments
]);
?>