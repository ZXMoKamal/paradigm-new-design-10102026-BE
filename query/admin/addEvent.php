<?php

include ("../../configs/header.php");
require_once("../../configs/connectDB.php");
require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/events.php");
require_once("../../controllers/storage/events.php");


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

$name = trim($_POST["name"] ?? "");
$description = trim($_POST["description"] ?? "");
$location = trim($_POST["location"] ?? "");
$client = trim($_POST["client"] ?? "");
$date = trim($_POST["date"] ?? "");


// ---------------------------------------------------------
// 5. Generate requestTitle
// ---------------------------------------------------------

$name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);

$requestTitle = preg_replace(
    '/\s+/',
    '-',
    trim($name)
);


// ---------------------------------------------------------
// 6. Validate fields
// ---------------------------------------------------------

if ($name === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Event name is required"
    ]);

    exit;
}


if ($description === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Event description is required"
    ]);

    exit;
}


if ($location === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Location is required"
    ]);

    exit;
}


if ($client === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Client is required"
    ]);

    exit;
}


if ($date === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Date is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 7. Check thumbnail
// ---------------------------------------------------------

if (
    !isset($_FILES["thumb"]) ||
    $_FILES["thumb"]["error"] !== UPLOAD_ERR_OK
) {

    http_response_code(400);

    echo json_encode([
        "error" => "Thumbnail image is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 8. Check slider images
// ---------------------------------------------------------

if (
    !isset($_FILES["sliderImages"]) ||
    !is_array($_FILES["sliderImages"]["name"])
) {

    http_response_code(400);

    echo json_encode([
        "error" => "Slider images are required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 9. Save event to database
// ---------------------------------------------------------

$event = addEvent(
    $name,
    $description,
    $client,
    $location,
    $date,
    $requestTitle,
    $user["uid"]
);


if (array_key_exists("error", $event)) {

    http_response_code(500);

    echo json_encode($event);

    exit;
}


$eventId = $event["id"];


// ---------------------------------------------------------
// 10. Save images
// ---------------------------------------------------------

$images = saveEventImages(
    $eventId,
    $_FILES["thumb"],
    $_FILES["sliderImages"],
    $user["uid"]
);


if (array_key_exists("error", $images)) {

    http_response_code(500);

    echo json_encode([
        "error" => $images["error"]
    ]);

    exit;
}


// ---------------------------------------------------------
// 11. Success
// ---------------------------------------------------------

http_response_code(201);

echo json_encode([
    "success" => true,
    "message" => "Event created successfully",
    "event" => [
        "id" => $eventId,
        "name" => $name,
        "description" => $description,
        "client" => $client,
        "location" => $location,
        "date" => $date,
        "requestTitle" => $requestTitle,
        "thumbnail" => $images["thumbnail"],
        "sliderImages" => $images["sliderImages"]
    ]
]);