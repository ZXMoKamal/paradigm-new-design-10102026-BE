<?php
include(__DIR__."/../../configs/connectDB.php");

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



function addEvent($name, $description, $client, $location, $date, $requestTitle)
{
    global $conn;

    $sql = "INSERT INTO events (name, description, client, location, date, requestTitle) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);
        return [
            "error" => "Failed to prepare database query"
        ];
    }

    $stmt->bind_param(
        "ssssss",
        $name,
        $description,
        $client,
        $location,
        $date,
        $requestTitle
    );

    if (!$stmt->execute()) {
        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to create event"
        ];
    }

    $eventId = $conn->insert_id;

    $stmt->close();

    return [
        "id" => $eventId
    ];
}




function deleteEvent($id)
{
    global $conn;

    $id = (int)$id;

    if ($id <= 0) {
        return [
            "error" => "Invalid event ID"
        ];
    }

    // Check if event exists
    $sql = "SELECT id FROM events WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);

        return [
            "error" => "Failed to prepare database query"
        ];
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {

        $stmt->close();

        return [
            "error" => "Event not found"
        ];
    }

    $stmt->close();


    // Delete event
    $sql = "DELETE FROM events WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);

        return [
            "error" => "Failed to prepare delete query"
        ];
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {

        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to delete event"
        ];
    }

    $stmt->close();

    return [
        "success" => true,
        "id" => $id
    ];
}