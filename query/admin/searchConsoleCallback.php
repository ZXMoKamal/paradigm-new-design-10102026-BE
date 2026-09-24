<?php

-   // $config = require __DIR__ . "/../../../../../../configs/paradigm-config.php";

    // if (!isset($_GET["code"])) {

    //     http_response_code(400);

    //     echo "Authorization code missing.";

    //     exit;
    // }

    // $code = $_GET["code"];

    // $postData = [
    //     "code" => $code,
    //     "client_id" => $config["googleCloudConsole"]["GOOGLE_CLIENT_ID"],
    //     "client_secret" =>  $config["googleCloudConsole"]["GOOGLE_CLIENT_SECRET"],
    //     "redirect_uri" => $config["googleCloudConsole"]["GOOGLE_REDIRECT_URI"],
    //     "grant_type" => "authorization_code"
    // ];

    // $ch = curl_init($config["googleCloudConsole"]["GOOGLE_TOKEN_URL"]);

    // curl_setopt_array($ch, [
    //     CURLOPT_POST => true,

    //     CURLOPT_POSTFIELDS => http_build_query($postData),

    //     CURLOPT_RETURNTRANSFER => true,

    //     CURLOPT_HTTPHEADER => [
    //         "Content-Type: application/x-www-form-urlencoded"
    //     ]
    // ]);

    // $response = curl_exec($ch);

    // if ($response === false) {

    //     http_response_code(500);

    //     echo "cURL error: " . curl_error($ch);

    //     curl_close($ch);

    //     exit;
    // }

    // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // curl_close($ch);

    // $data = json_decode($response, true);

    // if ($httpCode !== 200) {

    //     http_response_code($httpCode);

    //     echo "<pre>";
    //     print_r($data);
    //     echo "</pre>";

    //     exit;
    // }

    // echo "<pre>";
    // print_r($data);
    // echo "</pre>";
-




require_once("../../configs/connectDB.php");
// require_once("../../../../configs/paradigm-config.php");

$config = require __DIR__ . "/../../../../../../configs/paradigm-config.php";

$google = $config["googleCloudConsole"];


// --------------------------------------------------
// 1. Make sure Google sent us an authorization code
// --------------------------------------------------

if (!isset($_GET["code"]) || empty($_GET["code"])) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Authorization code missing."
    ]);

    exit;
}

$code = $_GET["code"];


// --------------------------------------------------
// 2. Exchange authorization code for Google tokens
// --------------------------------------------------

$postData = [
    "code" => $code,

    "client_id" => $google["GOOGLE_CLIENT_ID"],

    "client_secret" => $google["GOOGLE_CLIENT_SECRET"],

    "redirect_uri" => $google["GOOGLE_REDIRECT_URI"],

    "grant_type" => "authorization_code"
];


$ch = curl_init($google["GOOGLE_TOKEN_URL"]);

curl_setopt_array($ch, [

    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS => http_build_query($postData),

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded"
    ]
]);


$response = curl_exec($ch);


if ($response === false) {

    $error = curl_error($ch);

    curl_close($ch);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "cURL error: " . $error
    ]);

    exit;
}


$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);


$data = json_decode($response, true);


// --------------------------------------------------
// 3. Check Google's response
// --------------------------------------------------

if ($httpCode !== 200) {

    http_response_code($httpCode);

    echo json_encode([
        "success" => false,
        "error" => $data
    ]);

    exit;
}


// --------------------------------------------------
// 4. Make sure we received a refresh token
// --------------------------------------------------

if (
    !isset($data["refresh_token"]) ||
    empty($data["refresh_token"])
) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Google did not return a refresh token."
    ]);

    exit;
}


$refreshToken = $data["refresh_token"];

$scope = $data["scope"] ?? null;


// --------------------------------------------------
// 5. Save the refresh token
// --------------------------------------------------

$sql = "
    SELECT id
    FROM google_search_console_auth
    LIMIT 1
";

$result = mysqli_query($conn, $sql);


if (!$result) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => mysqli_error($conn)
    ]);

    exit;
}


$existing = mysqli_fetch_assoc($result);


// --------------------------------------------------
// 6. Update existing connection
// --------------------------------------------------

if ($existing) {

    $stmt = mysqli_prepare(
        $conn,
        "
        UPDATE google_search_console_auth
        SET
            refresh_token = ?,
            scope = ?
        WHERE id = ?
        "
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $refreshToken,
        $scope,
        $existing["id"]
    );


// --------------------------------------------------
// 7. Otherwise create first connection
// --------------------------------------------------

} else {

    $stmt = mysqli_prepare(
        $conn,
        "
        INSERT INTO google_search_console_auth
        (
            refresh_token,
            scope
        )
        VALUES (?, ?)
        "
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $refreshToken,
        $scope
    );
}


if (!mysqli_stmt_execute($stmt)) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => mysqli_stmt_error($stmt)
    ]);

    mysqli_stmt_close($stmt);

    exit;
}


mysqli_stmt_close($stmt);


// --------------------------------------------------
// 8. Authentication completed
// --------------------------------------------------

echo json_encode([
    "success" => true,
    "message" => "Google Search Console connected successfully."
]);