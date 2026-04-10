<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "quickaid");

$data = json_decode(file_get_contents("php://input"), true);
$postId = $data['postId'];
$userId = $data['userId'];
$comment = $data['comment'];

if(!$postId || !$userId || !$comment){
    echo json_encode(["success"=>false,"message"=>"Missing fields"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO comments (postId, userId, comment, createdAt) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iss", $postId, $userId, $comment);

if($stmt->execute()){
    echo json_encode(["success"=>true]);
}else{
    echo json_encode(["success"=>false,"message"=>"Insert failed"]);
}
$stmt->close();
$conn->close();
?>