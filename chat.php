<?php
header("Content-Type: application/json");


// 🔑 YOUR OPENROUTER KEY
$apiKey = "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";

// Get message
$data = json_decode(file_get_contents("php://input"), true);
$userMessage = $data["message"] ?? "";

// OpenRouter endpoint
$url = "https://openrouter.ai/api/v1/chat/completions";

// Request body
$postData = [
    "model" => "openai/gpt-3.5-turbo", // free & good
    "messages" => [
        ["role" => "system", "content" => "You are a helpful medical assistant. Give short, clear health advice. If serious, suggest doctor."],
        ["role" => "user", "content" => $userMessage]
    ]
];

// cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $apiKey,
    "Content-Type: application/json",
    "HTTP-Referer: http://localhost", // required
    "X-Title: QuickAid App"
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo json_encode(["reply" => "Curl Error: " . curl_error($ch)]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

// DEBUG ERROR
if(isset($result["error"])){
    echo json_encode(["reply" => "API Error: " . $result["error"]["message"]]);
    exit;
}

// Extract reply
$reply = $result["choices"][0]["message"]["content"] ?? "No reply";

echo json_encode(["reply" => $reply]);
?>