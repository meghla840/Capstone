<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "quickaid");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

// ✅ CORRECT JOIN (using users.id)
$sql = "SELECT 
            p.*,
            u.name AS authorName,
            u.role AS authorRole
        FROM forum_posts p
        LEFT JOIN users u ON p.userId = u.id
        ORDER BY p.createdAt DESC";

$result = $conn->query($sql);

$posts = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {

        $pid = $row['id'];
        $comments = [];

        // ✅ FIXED COMMENTS JOIN
        $commentQuery = "SELECT 
                            c.*,
                            u.name AS commentAuthor
                         FROM comments c
                         JOIN users u ON c.userId = u.id
                         WHERE c.postId = $pid
                         ORDER BY c.createdAt ASC";

        $commentsRes = $conn->query($commentQuery);

        if ($commentsRes) {
            while ($c = $commentsRes->fetch_assoc()) {
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
                "name" => !empty($row['authorName']) ? $row['authorName'] : 'Unknown User',
                "role" => !empty($row['authorRole']) ? $row['authorRole'] : 'user'
            ],

            "comments" => $comments
        ];
    }
}

echo json_encode($posts);
$conn->close();
?>