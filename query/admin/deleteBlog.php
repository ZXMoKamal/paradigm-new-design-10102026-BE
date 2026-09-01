<?php

include ("../../configs/header.php");

require_once("../../configs/connectDB.php");
require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/blogs.php");
require_once("../../controllers/storage/blogs.php");


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
        "error" => "Valid blog ID is required"
    ]);

    exit;
}

$id = (int)$id;


// ---------------------------------------------------------
// 6. Delete from database
// ---------------------------------------------------------

$result = deleteBlog($id);


if (array_key_exists("error", $result)) {

    // blog doesn't exist
    if ($result["error"] === "blog not found") {

        http_response_code(404);

    } else {

        http_response_code(500);
    }

    echo json_encode($result);

    exit;
}


// ---------------------------------------------------------
// 7. Delete event storage
// ---------------------------------------------------------

$storageResult = deleteBlogStorage($id);


if (array_key_exists("error", $storageResult)) {

    /*
     * At this point the DB record has already been deleted.
     *
     * Log the storage error so you can manually remove
     * the folder later.
     */

    error_log(
        "blog {$id} deleted from DB but storage deletion failed: " .
        $storageResult["error"]
    );

    http_response_code(500);

    echo json_encode([
        "error" => "blog deleted from database but failed to delete storage",
        "id" => $id
    ]);

    exit;
}


// ---------------------------------------------------------
// 8. Success
// ---------------------------------------------------------

http_response_code(200);

echo json_encode([
    "success" => true,
    "message" => "blog deleted successfully",
    "id" => $id
]);