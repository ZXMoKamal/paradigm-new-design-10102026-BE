<?php
include("../configs/connectDB.php");

function getAllEvents($from, $to) {

    global $conn;

    // Valnameate inputs
    $from = (int)$from;
    $to = (int)$to;

    $limit = $to - $from;

    if ($limit <= 0) {
        // http_response_code(400);
        return ["error" => "Invalid range"];
    }

    $sql = $sql = "SELECT id, name, requestTitle FROM events ORDER BY id DESC LIMIT ?, ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $from, $limit);
    $stmt->execute();

    $result = $stmt->get_result();

    $data = [];

    if (!$result || $result->num_rows == 0) {
        // http_response_code(404);
        return ["error" => "No data found"];
    }

    // http_response_code(200);
    $data = $result->fetch_all(MYSQLI_ASSOC);

    return $data;
}


function getEvent($requestTitle) {

    global $conn;

    $sql = "SELECT id, name, description, client, location, date, requestTitle FROM events WHERE requestTitle = ? LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $requestTitle);
    $stmt->execute();

    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        return ["error" => "No data found"];
    }

    // Fetch single row
    $data = $result->fetch_assoc();

    return $data;
}