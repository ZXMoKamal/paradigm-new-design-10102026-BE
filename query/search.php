<?php

// include("../configs/header.php");
// include("../controllers/db/search.php");

// if ($_SERVER["REQUEST_METHOD"] !== "GET") {

//     http_response_code(405);

//     echo json_encode([
//         "error" => "Method not allowed"
//     ]);

//     exit;
// }


// $search = $_GET["search"] ?? "";

// $results = searchData($search);

// if (array_key_exists("error", $results)) {

//     http_response_code(404);

//     echo json_encode([]);

//     exit;
// }

// http_response_code(200);

// echo json_encode(
//     $results,
//     JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
// );

include("../configs/header.php");
include("../controllers/db/search.php");


/*
|--------------------------------------------------------------------------
| Only allow GET
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    header("Allow: GET");

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get search parameter
|--------------------------------------------------------------------------
*/

$search = $_GET["search"] ?? "";


/*
|--------------------------------------------------------------------------
| Validate search parameter
|--------------------------------------------------------------------------
*/

// Must be a string
if (!is_string($search)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid search query"
    ]);

    exit;
}


// Remove unnecessary whitespace
$search = trim($search);


// Empty search
if ($search === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Search query is required"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Limit input length
|--------------------------------------------------------------------------
*/

// Prevent extremely large requests
if (mb_strlen($search, "UTF-8") > 100) {

    http_response_code(400);

    echo json_encode([
        "error" => "Search query is too long"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$results = searchData($search);


/*
|--------------------------------------------------------------------------
| Handle search errors
|--------------------------------------------------------------------------
*/

if (isset($results["error"])) {

    // "No results found" is not really a server error.
    if ($results["error"] === "No results found") {

        http_response_code(200);

        echo json_encode(
            [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }


    // Don't expose internal database errors
    http_response_code(500);

    echo json_encode([
        "error" => "Search failed"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Return results
|--------------------------------------------------------------------------
*/

http_response_code(200);

echo json_encode(
    $results,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES |
    JSON_INVALID_UTF8_SUBSTITUTE
);

exit;