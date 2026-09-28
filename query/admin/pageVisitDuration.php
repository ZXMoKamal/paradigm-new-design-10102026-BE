<?php

include("../../configs/header.php");
require_once("../../controllers/db/pageVisitDuration.php");

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$visitorId = $data["visitorId"] ?? null;
$itemId = $data["itemId"] ?? null;
$duration  = $data["duration"] ?? null;
$page  = $data["page"] ?? null;

if (!$visitorId || !$itemId || !$duration || !$page) {
    http_response_code(400);

    echo json_encode([
        "error" => "Missing required data"
    ]);

    exit;
}

$duration = (int)$duration;

if ($duration <= 0) {
    http_response_code(400);

    echo json_encode([
        "error" => "Invalid duration"
    ]);

    exit;
}

$result = addServiceDuration(
    $visitorId,
    (int)$itemId,
    $duration,
    $page
);

echo json_encode([
    "success" => $result
]);