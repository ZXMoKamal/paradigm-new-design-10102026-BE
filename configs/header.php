<?php

$config = require __DIR__ . "/../../../../../configs/paradigm-config.php";


    header('Content-Type: application/json; charset=utf-8');
    // header("Access-Control-Allow-Origin: ".$config["site_link"]);//. $_SERVER['HTTP_ORIGIN'] // *

    $allowed_origins = [
        $config["site_link"],
        $config["vercal_site_link"]
    ];

    $request_origin = $_SERVER["HTTP_ORIGIN"] ?? "";

    if (in_array($request_origin, $allowed_origins, true)) {
        header("Access-Control-Allow-Origin: " . $request_origin);
        header("Vary: Origin");
    }


    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, ngrok-skip-browser-warning");

    // header("X-Content-Type-Options: nosniff");
    // header("X-Frame-Options: DENY");
    // header("Referrer-Policy: strict-origin-when-cross-origin");
    // header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");  

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $input = json_decode(file_get_contents("php://input"), true);
?>