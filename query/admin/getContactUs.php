<?php

include ("../../configs/header.php");

require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/contactUs.php");

// Verify Firebase token
$user = verifyFirebaseToken();

// // Check admin
// if (!$user["admin"]) {
//     http_response_code(403);

//     echo json_encode([
//         "error" => "Admin access required"
//     ]);

//     exit;
// }

// Get contacts
$contacts = getAllContactUs(0,100);

if (array_key_exists("error", $contacts)) {

    http_response_code(404);

    echo json_encode($contacts);

    exit;
}

http_response_code(200);

echo json_encode(
    $contacts,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);