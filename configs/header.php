<?php

$config = require __DIR__ . "/../../../../../configs/paradigm-config.php";

    header('Content-Type: application/json; charset=utf-8');
    header("Access-Control-Allow-Origin: ".$config["site_link"]);//. $_SERVER['HTTP_ORIGIN'] // *
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, Post, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $input = json_decode(file_get_contents("php://input"), true);
?>