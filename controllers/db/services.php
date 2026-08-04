<?php

//all
//byid

function getAllServices() {
     
    global $conn;

    $sql = "SELECT id, title, icon_code FROM services";
    $result = $conn->query($sql);

    $data = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    return $data;
}


function getServiceWithHighlights($id) {

    global $conn;

    // Prepare statement for main service
    $stmt = $conn->prepare("SELECT id, title, icon_code FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $service = $result->fetch_assoc();

    if (!$service) {
        return null; // not found
    }

    // Get related highlights
    $stmt2 = $conn->prepare("SELECT id, highlight FROM highlights WHERE service_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    $result2 = $stmt2->get_result();
    $highlights = [];

    while ($row = $result2->fetch_assoc()) {
        $highlights[] = $row;
    }

    // Attach highlights
    $service['highlights'] = $highlights;

    return $service;
}