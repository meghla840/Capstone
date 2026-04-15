<?php
header("Content-Type: application/json");
include "backend/db.php"; // ✅ IMPORTANT

// 🔑 API KEY (keep safe later)
$apiKey = "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";

// Get message
$data = json_decode(file_get_contents("php://input"), true);
$userMessage = $data["message"] ?? "";

// OpenRouter endpoint
$url = "https://openrouter.ai/api/v1/chat/completions";

// Request body
$postData = [
    "model" => "openai/gpt-3.5-turbo",
    "messages" => [
        [
            "role" => "system",
            "content" => "You are a medical assistant.

Based on user symptoms:
1. Suggest doctor type ONLY from: Cardiologist,Eye, Medicine, Cardiologist, Brain,Dermatologist,Orthopedic
2. Give short advice

Respond ONLY in JSON like:
{
  \"doctor\": \"Eye\",
  \"message\": \"You may have eye strain\"
}"
        ],
        [
            "role" => "user",
            "content" => $userMessage
        ]
    ]
];

// cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $apiKey,
    "Content-Type: application/json",
    "HTTP-Referer: http://localhost",
    "X-Title: QuickAid App"
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["message" => "Curl Error"]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

if (isset($result["error"])) {
    echo json_encode(["message" => "API Error"]);
    exit;
}

// ✅ AI response
$reply = $result["choices"][0]["message"]["content"] ?? "{}";
$decoded = json_decode($reply, true);

$doctorType = $decoded["doctor"] ?? "Brain";
$message = $decoded["message"] ?? "No response";

// ================= FETCH DOCTORS =================
$doctors = [];

$stmt = $conn->prepare("
    SELECT d.*, u.name AS doctorName
    FROM doctors d
    JOIN users u ON d.userId = u.userId
    WHERE d.specialization LIKE ?
");

$search = "%$doctorType%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result2 = $stmt->get_result();

while ($row = $result2->fetch_assoc()) {
    $doctors[] = [
    "id" => $row['userId'], // 🔥 IMPORTANT
    "name" => $row['doctorName'],
    "specialization" => $row['specialization'],
    "clinic" => $row['clinic'],
    "bmdc" => $row['bmdc'],
    "fees" => $row['consultationFees'],
    "experience" => $row['experienceYears']
];
}

// ⭐ SORT BEST DOCTOR
usort($doctors, function($a, $b) {
    return $b['experience'] - $a['experience'];
});

// ================= FINAL RESPONSE =================
echo json_encode([
    "message" => $message,
    "doctorType" => $doctorType,
    "allDoctors" => $doctors,
    "bestDoctor" => $doctors[0] ?? null
]);