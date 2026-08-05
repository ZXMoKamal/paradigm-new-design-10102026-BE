<?php
include("../configs/connectDB.php");

function getAllEvents($from, $to) {

    global $conn;

    // Validate inputs
    $from = (int)$from;
    $to = (int)$to;

    $limit = $to - $from;

    if ($limit <= 0) {
        // http_response_code(400);
        return ["error" => "Invalid range"];
    }

    $sql = "SELECT id, name FROM events LIMIT ?, ?";

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


function getEvent($id) {

    global $conn;

    // Validate input
    $id = (int)$id;

    if ($id <= 0) {
        return ["error" => "Invalid ID"];
    }

    $sql = "SELECT id, name, description, client, location, date FROM events WHERE id = ? LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        return ["error" => "No data found"];
    }

    // Fetch single row
    $data = $result->fetch_assoc();

    return $data;
}