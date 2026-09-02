<?php

include ("../../configs/header.php");
include("../../controllers/auth/auth.php");


// Read JSON body
$input = json_decode(file_get_contents("php://input"), true);

$captchaToken = $input["captchaToken"] ?? "";

// CAPTCHA token is required
if (empty($captchaToken)) {
    http_response_code(400);

    echo json_encode([
        "error" => "Captcha verification required"
    ]);

    exit;
}

if(!verifyCaptcha($captcha)){
    http_response_code(403);
    echo json_encode(["error" => "Captcha verification failed"]);
    exit;
};

$user = verifyFirebaseToken();

echo json_encode([
    "message" => "Authenticated successfully",
    "uid" => $user["uid"],
    "name" => $user["name"]
]);