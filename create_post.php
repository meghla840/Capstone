<?php
session_start();
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "quickaid");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// ✅ CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

// ✅ IMPORTANT: this MUST be users.id (INT)
$userId = $_SESSION['user_id'];

// ✅ GET DATA FROM JS
$data = json_decode(file_get_contents("php://input"), true);

$title    = $data['title'] ?? '';
$content  = $data['content'] ?? '';
$category = $data['category'] ?? '';
$image    = $data['image'] ?? null;

// ✅ VALIDATION
if (!$title || !$content || !$category) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

// ✅ INSERT (NO ROLE)
$stmt = $conn->prepare("
    INSERT INTO forum_posts (userId, title, content, category, image, createdAt) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param("issss", $userId, $title, $content, $category, $image);

if($stmt->execute()){
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>