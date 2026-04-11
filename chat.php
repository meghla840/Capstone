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
    "model" => "openai/gpt-3.5-turbo",
    "messages" => [
        [
            "role" => "system",
            "content" => "You are a medical assistant.

Based on user symptoms:
1. Suggest doctor type ONLY from: Eye, Medicine, Cardiologist, Brain
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
    echo json_encode(["reply" => "Curl Error"]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

if (isset($result["error"])) {
    echo json_encode(["reply" => "API Error"]);
    exit;
}

$reply = $result["choices"][0]["message"]["content"] ?? "{}";

echo json_encode(["reply" => $reply]);
?>