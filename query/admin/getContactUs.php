<?php

// include ("../../configs/header.php");

    // require_once("../../controllers/auth/auth.php");
    // require_once("../../controllers/db/contactUs.php");

    // // Verify Firebase token
    // $user = verifyFirebaseToken();

    // // // Check admin
    // if (!$user["admin"]) {
    //     http_response_code(403);

    //     echo json_encode([
    //         "error" => "Admin access required"
    //     ]);

    //     exit;
    // }


    // // Check if search parameter exists
    // if (isset($_GET["search"])) {

    //     $search = trim($_GET["search"]);

    //     // Get all contacts for searching
    //     $contacts = getAllContactUs(0, 1000000, $search,$user["uid"]);

    // } else {

    //     // No search parameter -> keep your existing behavior
    //     $contacts = getAllContactUs(0, 100,null,$user["uid"]);

    // }


    // if (array_key_exists("error", $contacts)) {

    //     http_response_code(404);

    //     echo json_encode($contacts);

    //     exit;
    // }

    // http_response_code(200);

    // echo json_encode(
    //     $contacts,
    //     JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    // );

-



include("../../configs/header.php");

require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/contactUs.php");

// Verify Firebase token
$user = verifyFirebaseToken();

// Check admin
if (!$user["admin"]) {

    http_response_code(403);

    echo json_encode([
        "error" => "Admin access required"
    ]);

    exit;
}


// ---------------------------------------------------------
// Get parameters
// ---------------------------------------------------------

$search   = trim($_GET["search"] ?? "");
$filterBy = trim($_GET["filterBy"] ?? "");
$groupBy  = trim($_GET["groupBy"] ?? "");

// ---------------------------------------------------------
// Pagination
// ---------------------------------------------------------

$page = max(1, (int)($_GET["page"] ?? 1));

$limit = max(1, (int)($_GET["limit"] ?? 10));

$from = ($page - 1) * $limit;

$to = $from + $limit;


// ---------------------------------------------------------
// Get contacts
// ---------------------------------------------------------

$contacts = getAllContactUs(
    $from,
    $to,
    $search,
    $user["uid"],
    $filterBy,
    $groupBy
);


// ---------------------------------------------------------
// Handle error
// ---------------------------------------------------------

if (is_array($contacts) && array_key_exists("error", $contacts)) {

    http_response_code(404);

    echo json_encode($contacts);

    exit;
}


// ---------------------------------------------------------
// Response
// ---------------------------------------------------------

http_response_code(200);

echo json_encode(
    $contacts,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
