<?php
function getUserNamefromDB($id) {

    if (!is_numeric($id)) {
        throw new Exception("Invalid ID");
    }

    if ($id == 88) {
        return "automatic";
    }
    
global $conn;
    $sql = "SELECT name FROM admins WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement");
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return null;
    }

    $row = $result->fetch_assoc();

    return $row["name"];
}