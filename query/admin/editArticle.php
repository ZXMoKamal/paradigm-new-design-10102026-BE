<?php

include("../../configs/header.php");

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

$blogId = (int)($_POST["id"] ?? 0);

$name = trim($_POST["name"] ?? "");

$content = trim($_POST["content"] ?? "");


// ---------------------------------------------------------
// 5. Validate ID
// ---------------------------------------------------------

if ($blogId <= 0) {

    http_response_code(400);

    echo json_encode([
        "error" => "Blog ID is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 6. Validate fields
// ---------------------------------------------------------

if ($name === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Blog name is required"
    ]);

    exit;
}


if ($content === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Blog content is required"
    ]);

    exit;
}


// ---------------------------------------------------------
// 7. Validate content JSON
// ---------------------------------------------------------
//
// The frontend sends:
// JSON.stringify(paragraphs)
//
// Example:
// [
//     {
//         "title": "Introduction",
//         "paragraph": "Some text..."
//     }
// ]
//

$decodedContent = json_decode($content, true);

if (
    json_last_error() !== JSON_ERROR_NONE ||
    !is_array($decodedContent)
) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid blog content format"
    ]);

    exit;
}


// ---------------------------------------------------------
// 8. Generate requestTitle
// ---------------------------------------------------------

// $requestTitle = preg_replace(
//     '/\s+/',
//     '-',
//     trim($name)
// );


// ---------------------------------------------------------
// 9. Check if blog exists
// ---------------------------------------------------------

$existingBlog = getBlogById($blogId);

if (!$existingBlog) {

    http_response_code(404);

    echo json_encode([
        "error" => "Blog not found"
    ]);

    exit;
}


// ---------------------------------------------------------
// 10. Check thumbnail
// ---------------------------------------------------------
//
// Thumbnail is OPTIONAL during edit.
//
// If no new thumbnail is sent:
//     keep the existing thumbnail.
//
// If a new thumbnail is sent:
//     replace the existing thumbnail.
//

$newThumb = null;

if (
    isset($_FILES["thumb"]) &&
    $_FILES["thumb"]["error"] === UPLOAD_ERR_OK
) {

    $newThumb = $_FILES["thumb"];
}


// ---------------------------------------------------------
// 11. Update database
// ---------------------------------------------------------

$blog = updateBlog(
    $blogId,
    $name,
    $content,
    // $requestTitle,
    $user["uid"]
);


if (array_key_exists("error", $blog)) {

    http_response_code(500);

    echo json_encode($blog);

    exit;
}


// ---------------------------------------------------------
// 12. Update thumbnail
// ---------------------------------------------------------

$images = updateBlogImages(
    $blogId,
    $newThumb
);


if (array_key_exists("error", $images)) {

    http_response_code(500);

    echo json_encode([
        "error" => $images["error"]
    ]);

    exit;
}


// ---------------------------------------------------------
// 13. Success
// ---------------------------------------------------------

http_response_code(200);

echo json_encode([
    "success" => true,

    "message" => "Blog updated successfully",

    "blog" => [
        "id" => $blogId,

        "name" => $name,

        "content" => $content,

        // "requestTitle" => $requestTitle,

        "thumbnail" => $images["thumbnail"],

        "lastUpdateBy" => $blog["lastUpdateBy"],

        "lastUpdateOn" => $blog["lastUpdateOn"]
    ]
]);