<?php

include("../../configs/header.php");

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
// 4. Get event ID
// ---------------------------------------------------------

$eventId = intval($_POST["id"] ?? 0);


if ($eventId <= 0) {

    http_response_code(400);

    echo json_encode([
        "error" => "Valid event ID is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 5. Get form data
// ---------------------------------------------------------

$name = trim($_POST["name"] ?? "");

$description = trim($_POST["description"] ?? "");

$location = trim($_POST["location"] ?? "");

$client = trim($_POST["client"] ?? "");

$date = trim($_POST["date"] ?? "");


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


// // ---------------------------------------------------------
// // 7. Generate requestTitle
// // ---------------------------------------------------------

// $requestTitle = preg_replace(
//     '/\s+/',
//     '-',
//     trim($name)
// );


// ---------------------------------------------------------
// 8. Get existing slider images to keep
// ---------------------------------------------------------

$existingSliderImages =
    $_POST["existingSliderImages"] ?? [];


// Make sure it is an array

if (!is_array($existingSliderImages)) {

    $existingSliderImages = [
        $existingSliderImages
    ];
}


// ---------------------------------------------------------
// 9. Get new thumbnail
// ---------------------------------------------------------

$newThumb = null;

if (
    isset($_FILES["thumb"]) &&
    $_FILES["thumb"]["error"] === UPLOAD_ERR_OK
) {

    $newThumb = $_FILES["thumb"];
}


// ---------------------------------------------------------
// 10. Get new slider images
// ---------------------------------------------------------

$newSliderImages = null;

if (
    isset($_FILES["sliderImages"]) &&
    isset($_FILES["sliderImages"]["name"]) &&
    is_array($_FILES["sliderImages"]["name"])
) {

    $newSliderImages = $_FILES["sliderImages"];
}


// ---------------------------------------------------------
// 11. Check event exists
// ---------------------------------------------------------

$existingEvent = getEventById($eventId,true,$user["uid"]);


if (array_key_exists("error", $existingEvent)) {

    http_response_code(500);

    echo json_encode($existingEvent);

    exit;
}


if (!$existingEvent) {

    http_response_code(404);

    echo json_encode([
        "error" => "Event not found"
    ]);

    exit;
}


// ---------------------------------------------------------
// 12. Update event in database
// ---------------------------------------------------------

$event = updateEvent(
    $eventId,
    $name,
    $description,
    $client,
    $location,
    $date,
    // $requestTitle,
    $user["uid"]
);


if (array_key_exists("error", $event)) {

    http_response_code(500);

    echo json_encode($event);

    exit;
}


// ---------------------------------------------------------
// 13. Update images
// ---------------------------------------------------------

$images = updateEventImages(
    $eventId,
    $newThumb,
    $existingSliderImages,
    $newSliderImages,
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
// 14. Success
// ---------------------------------------------------------

http_response_code(200);

echo json_encode([
    "success" => true,

    "message" => "Event updated successfully",

    "event" => [
        "id" => $eventId,

        "name" => $name,

        "description" => $description,

        "client" => $client,

        "location" => $location,

        "date" => $date,

        "thumbnail" => $images["thumbnail"],

        "sliderImages" => $images["sliderImages"]
    ]
]);