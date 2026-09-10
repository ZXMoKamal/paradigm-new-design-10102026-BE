<?php

include ("../../configs/header.php");
require_once("../../configs/connectDB.php");
require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/blogs.php");
require_once("../../controllers/storage/blogs.php");


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
$content = trim($_POST["content"] ?? "");


// ---------------------------------------------------------
// 5. Generate requestTitle
// ---------------------------------------------------------

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
        "error" => "blog name is required"
    ]);

    exit;
}


if ($content === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "blog description is required"
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
// 8. Save event to database
// ---------------------------------------------------------

$blog = addBlog(
    $name,
    $content,
    $requestTitle
);


if (array_key_exists("error", $blog)) {

    http_response_code(500);

    echo json_encode($blog);

    exit;
}


$blogId = $blog["id"];


// ---------------------------------------------------------
// 10. Save images
// ---------------------------------------------------------

$images = saveBlogImages(
    $blogId,
    $_FILES["thumb"]
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
    "message" => "blog created successfully",
    "blog" => [
        "id" => $blogId,
        "name" => $name,
        "content" => $content,
        "requestTitle" => $requestTitle,
        "thumbnail" => $images["thumbnail"],
    ]
]);