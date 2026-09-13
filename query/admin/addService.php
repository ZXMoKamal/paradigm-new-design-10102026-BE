<?php

include ("../../configs/header.php");
require_once("../../configs/connectDB.php");
require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/services.php");


// ---------------------------------------------------------
// 1. Verify Firebase authentication
// ---------------------------------------------------------

$user = verifyFirebaseToken();


// ---------------------------------------------------------
// 2. Check admin
// ---------------------------------------------------------

if ($user["admin"] !== true) {

    http_response_code(403);

    echo json_encode([
        "error" => "Admin access required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 3. Check request method
// ---------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}


// ---------------------------------------------------------
// 4. Get form data
// ---------------------------------------------------------

$title = trim($_POST["title"] ?? "");

$iconCode = trim($_POST["iconCode"] ?? "");

// ---------------------------------------------------------
// 5. Generate requestTitle
// ---------------------------------------------------------

$requestTitle = preg_replace(
    '/\s+/',
    '-',
    trim($title)
);


// ---------------------------------------------------------
// 5. Get highlights
// ---------------------------------------------------------

$highlights = $_POST["highlights"] ?? [];


// If highlights was sent as JSON string
if (is_string($highlights)) {

    $decodedHighlights = json_decode(
        $highlights,
        true
    );

    if (
        json_last_error() === JSON_ERROR_NONE &&
        is_array($decodedHighlights)
    ) {
        $highlights = $decodedHighlights;
    }
}


// ---------------------------------------------------------
// 6. Validate title
// ---------------------------------------------------------

if ($title === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Service title is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 7. Validate icon
// ---------------------------------------------------------

if ($iconCode === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Service icon is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 8. Validate highlights
// ---------------------------------------------------------

if (!is_array($highlights)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Highlights must be an array"
    ]);

    exit;
}


// ---------------------------------------------------------
// 9. Add service
// ---------------------------------------------------------

$service = addService(
    $title,
    $iconCode,
    $requestTitle,
    $user["uid"]
);


if (array_key_exists("error", $service)) {

    http_response_code(500);

    echo json_encode($service);

    exit;
}


$serviceId = $service["id"];


// ---------------------------------------------------------
// 10. Add highlights
// ---------------------------------------------------------

$highlightResult = addServiceHighlights(
    $serviceId,
    $highlights,
    $user["uid"]
);


if (array_key_exists("error", $highlightResult)) {

    http_response_code(500);

    echo json_encode([
        "error" => $highlightResult["error"]
    ]);

    exit;
}


// ---------------------------------------------------------
// 11. Success
// ---------------------------------------------------------

http_response_code(201);

echo json_encode([
    "success" => true,
    "message" => "Service created successfully",

    "service" => [
        "id" => $serviceId,
        "title" => $title,
        "icon_code" => $iconCode,
        "highlights" => $highlights
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);