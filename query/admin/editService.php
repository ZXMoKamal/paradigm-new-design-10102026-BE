<?php

include("../../configs/header.php");

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
// 4. Get service ID
// ---------------------------------------------------------

$serviceId = (int)($_POST["id"] ?? 0);


// ---------------------------------------------------------
// 5. Get form data
// ---------------------------------------------------------

$title = trim($_POST["title"] ?? "");

$iconCode = trim($_POST["icon_code"] ?? "");

$highlights = $_POST["highlights"] ?? [];


// ---------------------------------------------------------
// 6. Decode highlights if sent as JSON
// ---------------------------------------------------------

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

    } else {

        http_response_code(400);

        echo json_encode([
            "error" => "Invalid highlights data"
        ]);

        exit;
    }
}


// ---------------------------------------------------------
// 7. Validate service ID
// ---------------------------------------------------------

if ($serviceId <= 0) {

    http_response_code(400);

    echo json_encode([
        "error" => "Valid service ID is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 8. Validate title
// ---------------------------------------------------------

if ($title === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Service title is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 9. Validate icon
// ---------------------------------------------------------

if ($iconCode === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Service icon is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 10. Validate highlights
// ---------------------------------------------------------

if (!is_array($highlights)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Highlights must be an array"
    ]);

    exit;
}


// ---------------------------------------------------------
// 11. Get existing service
// ---------------------------------------------------------

$existingService = getServiceById($serviceId);

if (array_key_exists("error", $existingService)) {

    http_response_code(500);

    echo json_encode($existingService);

    exit;
}

if (!$existingService) {

    http_response_code(404);

    echo json_encode([
        "error" => "Service not found"
    ]);

    exit;
}


// ---------------------------------------------------------
// 12. Generate requestTitle
// ---------------------------------------------------------

// $requestTitle = preg_replace(
//     '/\s+/',
//     '-',
//     trim($title)
// );


// ---------------------------------------------------------
// 13. Update service
// ---------------------------------------------------------

$service = updateService(
    $serviceId,
    $title,
    $iconCode,
    // $requestTitle,
    $user["uid"]
);


if (array_key_exists("error", $service)) {

    http_response_code(500);

    echo json_encode($service);

    exit;
}


// ---------------------------------------------------------
// 14. Replace service highlights
// ---------------------------------------------------------

$highlightResult = updateServiceHighlights(
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
// 15. Success
// ---------------------------------------------------------

http_response_code(200);

echo json_encode([

    "success" => true,

    "message" => "Service updated successfully",

    "service" => [

        "id" => $serviceId,

        "title" => $title,

        "icon_code" => $iconCode,

        // "requestTitle" => $requestTitle,

        "highlights" => $highlights,

        "lastUpdateBy" => $service["lastUpdateBy"],

        "lastUpdateOn" => $service["lastUpdateOn"]

    ]

], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);