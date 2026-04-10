<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "quickaid");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

// ✅ FIXED JOIN + added role
$sql = "SELECT p.*, u.name AS authorName, u.role 
        FROM forum_posts p 
      LEFT JOIN users u ON p.userId = u.userId
        ORDER BY p.createdAt DESC";

$result = $conn->query($sql);
$posts = [];

if ($result) {
    while($row = $result->fetch_assoc()){
        $pid = $row['id'];

        $comments = [];

        // ✅ FIXED comment JOIN
        $commentQuery = "SELECT c.*, u.name AS commentAuthor 
                         FROM comments c 
                         JOIN users u ON c.userId = u.userId
                         WHERE c.postId = $pid";

        $commentsRes = $conn->query($commentQuery);

        if ($commentsRes) {
            while($c = $commentsRes->fetch_assoc()){
                $comments[] = [
                    "author" => $c['commentAuthor'],
                    "text" => $c['comment']
                ];
            }
        }

        $posts[] = [
            "id" => $row['id'],
            "title" => $row['title'],
            "content" => $row['content'],
            "category" => $row['category'],
            "image" => $row['image'],
            "createdAt" => $row['createdAt'],

            "author" => [
                "userId" => $row['userId'],
                "name" => $row['authorName'] ?? 'Unknown User',
                "role" => $row['role'] ?? 'user'
            ],

            "comments" => $comments
        ];
    }
}

echo json_encode($posts);
$conn->close();
?>