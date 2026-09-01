<?php

include ("../../configs/header.php");

require_once("../../configs/connectDB.php");
require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/services.php");


// ---------------------------------------------------------
// 1. Verify Firebase token
// ---------------------------------------------------------

$user = verifyFirebaseToken();


// ---------------------------------------------------------
// 2. Check admin
// ---------------------------------------------------------

// if ($user["admin"] !== true) {

//     http_response_code(403);

//     echo json_encode([
//         "error" => "Admin access required"
//     ]);

//     exit;
// }


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

$id = $_POST["id"] ?? null;


// ---------------------------------------------------------
// 5. Validate ID
// ---------------------------------------------------------

if (
    $id === null ||
    !ctype_digit((string)$id) ||
    (int)$id <= 0
) {

    http_response_code(400);

    echo json_encode([
        "error" => "Valid event ID is required"
    ]);

    exit;
}

$id = (int)$id;


// ---------------------------------------------------------
// 6. Delete from database
// ---------------------------------------------------------

$result = deleteService($id);


if (array_key_exists("error", $result)) {

    // Service doesn't exist
    if ($result["error"] === "Service not found") {

        http_response_code(404);

    } else {

        http_response_code(500);
    }

    echo json_encode($result);

    exit;
}

// ---------------------------------------------------------
// 8. Success
// ---------------------------------------------------------

http_response_code(200);

echo json_encode([
    "success" => true,
    "message" => "Service deleted successfully",
    "id" => $id
]);