<?php
session_start();
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "quickaid");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// ✅ GET USER FROM SESSION (NOT FROM JS)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

$userId = $_SESSION['user_id'];

// ✅ GET DATA FROM JS
$data = json_decode(file_get_contents("php://input"), true);

$title    = $data['title'] ?? '';
$content  = $data['content'] ?? '';
$category = $data['category'] ?? '';
$image    = $data['image'] ?? null;
$postRole = $data['role'] ?? 'user';

// ✅ VALIDATION
if (!$title || !$content || !$category) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

// ✅ INSERT
$stmt = $conn->prepare("INSERT INTO forum_posts (userId, title, content, category, image, role, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())");

$stmt->bind_param("isssss", $userId, $title, $content, $category, $image, $postRole);

if($stmt->execute()){
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>