<?php

require_once("../controllers/searchConsole/statistics.php");

header("Content-Type: application/json");

try {

    $siteUrl = "https://yourdomain.com/";

    $data = getSearchConsoleData(
        $siteUrl,
        "2026-08-25",
        "2026-09-21",
        ["date"],
        1000
    );

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}