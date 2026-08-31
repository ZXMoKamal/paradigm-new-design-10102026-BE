<?php
include(__DIR__."/../../configs/connectDB.php");

function getAllBlogs($from, $to) {

    global $conn;

    // Valnameate inputs
    $from = (int)$from;
    $to = (int)$to;

    $limit = $to - $from;

    if ($limit <= 0) {
        // http_response_code(400);
        return ["error" => "Invalid range"];
    }

    $sql = $sql = "SELECT id, name, requestTitle FROM blogs ORDER BY id DESC LIMIT ?, ?";

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


function getBlog($requestTitle) {

    global $conn;

    $sql = "SELECT id, name, content, requestTitle FROM blogs WHERE requestTitle = ? LIMIT 1";

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


function addBlog($name, $content, $requestTitle)
{
    global $conn;

    $sql = "INSERT INTO blogs (name, content, requestTitle) VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);
        return [
            "error" => "Failed to prepare database query"
        ];
    }

    $stmt->bind_param(
        "sss",
        $name,
        $content,
        $requestTitle
    );

    if (!$stmt->execute()) {
        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to create event"
        ];
    }

    $blogtId = $conn->insert_id;

    $stmt->close();

    return [
        "id" => $blogtId
    ];
}