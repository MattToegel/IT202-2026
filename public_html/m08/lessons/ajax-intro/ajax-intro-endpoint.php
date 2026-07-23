<?php
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    http_response_code(405);
    echo json_encode([
        "ok" => false,
        "message" => "Use a POST request for this endpoint.",
    ]);
    exit;
}

$message = $_POST["message"] ?? "";

if (!is_string($message) || trim($message) === "") {
    http_response_code(400);
    echo json_encode([
        "ok" => false,
        "message" => "Enter a message before sending the request.",
    ]);
    exit;
}

echo json_encode([
    "ok" => true,
    "message" => "The server received: " . trim($message),
]);
