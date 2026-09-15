<?php
include(__DIR__."/../../configs/connectDB.php");
    require_once(__DIR__."/getAdmins.php");
    
// function getAllEvents($from, $to) {

//     global $conn;

//     // Valnameate inputs
//     $from = (int)$from;
//     $to = (int)$to;

//     $limit = $to - $from;

//     if ($limit <= 0) {
//         // http_response_code(400);
//         return ["error" => "Invalid range"];
//     }

//     $sql = $sql = "SELECT id, name, requestTitle FROM events ORDER BY id DESC LIMIT ?, ?";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ii", $from, $limit);
//     $stmt->execute();

//     $result = $stmt->get_result();

//     $data = [];

//     if (!$result || $result->num_rows == 0) {
//         // http_response_code(404);
//         return ["error" => "No data found"];
//     }

//     // http_response_code(200);
//     $data = $result->fetch_all(MYSQLI_ASSOC);

//     return $data;
// }
function getAllEvents($from, $to, $isAdmin = false)
{
    global $conn;

    // Validate inputs
    $from = (int)$from;
    $to = (int)$to;

    $limit = $to - $from;

    if ($limit <= 0) {
        return ["error" => "Invalid range"];
    }


    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "SELECT 
                    id,
                    name,
                    requestTitle
                FROM events
                ORDER BY id DESC
                LIMIT ?, ?";

    }


    /*
     * Admin users
     */
    else {

        $sql = "SELECT 
                    id,
                    name,
                    requestTitle,
                    createdOn,
                    createdBy,
                    lastUpdateOn,
                    lastUpdateBy
                FROM events
                ORDER BY id DESC
                LIMIT ?, ?";
    }


    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $stmt->bind_param("ii", $from, $limit);

    if (!$stmt->execute()) {
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();


    if (!$result || $result->num_rows == 0) {
        return ["error" => "No data found"];
    }


    $data = $result->fetch_all(MYSQLI_ASSOC);


    /*
     * Only admins can see creator information.
     */
    if ($isAdmin) {

        foreach ($data as &$event) {

            if ($event["createdBy"] !== null) {

                $event["createdBy"] = getUserNamefromDB(
                    (int)$event["createdBy"]
                );
            }
            if ($event["lastUpdateBy"] !== null) {

                $event["lastUpdateBy"] = getUserNamefromDB(
                    (int)$event["lastUpdateBy"]
                );
            }
        }

        unset($event);
    }


    $stmt->close();

    return $data;
}


// function getEvent($requestTitle) {

//     global $conn;

//     $sql = "SELECT id, name, description, client, location, date, requestTitle FROM events WHERE requestTitle = ? LIMIT 1";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $requestTitle);
//     $stmt->execute();

//     $result = $stmt->get_result();

//     if (!$result || $result->num_rows == 0) {
//         return ["error" => "No data found"];
//     }

//     // Fetch single row
//     $data = $result->fetch_assoc();

//     return $data;
// }

function getEvent($requestTitle, $isAdmin = false, $visitorId = null)
{
    global $conn;


    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "SELECT 
                    id,
                    name,
                    description,
                    client,
                    location,
                    date,
                    requestTitle
                FROM events
                WHERE requestTitle = ?
                LIMIT 1";

    }


    /*
     * Admin users
     */
    else {

        $sql = "SELECT 
                    id,
                    name,
                    description,
                    client,
                    location,
                    date,
                    requestTitle,
                    createdOn,
                    createdBy
                FROM events
                WHERE requestTitle = ?
                LIMIT 1";
    }


    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $stmt->bind_param("s", $requestTitle);

    if (!$stmt->execute()) {
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();


    if (!$result || $result->num_rows == 0) {
        return ["error" => "No data found"];
    }


    // Fetch single row
    $data = $result->fetch_assoc();


    /*
     * Only admins can see creator information.
     */
    if ($isAdmin) {

        if ($data["createdBy"] !== null) {

            $data["createdBy"] = getUserNamefromDB(
                (int)$data["createdBy"]
            );
        }
    }


    $stmt->close();


        if ($visitorId !== null && trim($visitorId) !== "") {
            $date = date("Y-m-d");

        $sql = "
            INSERT IGNORE INTO events_visits
            (visitorId, eventId, date)
            VALUES (?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare query"
            ];
        }

        $stmt->bind_param(
            "sis",
            $visitorId,
            $data["id"],
            $date
        );

        if (!$stmt->execute()) {
            error_log($stmt->error);

            $stmt->close();

            return [
                "error" => "Failed to record event visit"
            ];
        }

        $isNewVisit = $stmt->affected_rows === 1;

        $stmt->close();
    }


    return $data;
}

function getEventById($eventId, $isAdmin = false, $visitorId = null)
{
    global $conn;


    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "SELECT 
                    id,
                    name,
                    description,
                    client,
                    location,
                    date,
                    requestTitle
                FROM events
                WHERE id = ?
                LIMIT 1";

    }


    /*
     * Admin users
     */
    else {

        $sql = "SELECT 
                    id,
                    name,
                    description,
                    client,
                    location,
                    date,
                    requestTitle,
                    createdOn,
                    createdBy
                FROM events
                WHERE id = ?
                LIMIT 1";
    }


    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $stmt->bind_param("s", $eventId);

    if (!$stmt->execute()) {
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();


    if (!$result || $result->num_rows == 0) {
        return ["error" => "No data found"];
    }


    // Fetch single row
    $data = $result->fetch_assoc();


    /*
     * Only admins can see creator information.
     */
    if ($isAdmin) {

        if ($data["createdBy"] !== null) {

            $data["createdBy"] = getUserNamefromDB(
                (int)$data["createdBy"]
            );
        }
    }


    $stmt->close();


        if ($visitorId !== null && trim($visitorId) !== "") {
            $date = date("Y-m-d");

        $sql = "
            INSERT IGNORE INTO events_visits
            (visitorId, eventId, date)
            VALUES (?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare query"
            ];
        }

        $stmt->bind_param(
            "sis",
            $visitorId,
            $data["id"],
            $date
        );

        if (!$stmt->execute()) {
            error_log($stmt->error);

            $stmt->close();

            return [
                "error" => "Failed to record event visit"
            ];
        }

        $isNewVisit = $stmt->affected_rows === 1;

        $stmt->close();
    }


    return $data;
}



function addEvent($name, $description, $client, $location, $date, $requestTitle,$uid)
{
    global $conn;

        /*
     * Get admin database ID using Firebase UID
     */
    $adminSql = "SELECT id FROM admins WHERE fbuid = ? LIMIT 1";

    $adminStmt = $conn->prepare($adminSql);

    if (!$adminStmt) {
        error_log($conn->error);

        return [
            "error" => "Failed to prepare admin query"
        ];
    }

    $adminStmt->bind_param(
        "s",
        $uid
    );

    if (!$adminStmt->execute()) {
        error_log($adminStmt->error);

        $adminStmt->close();

        return [
            "error" => "Failed to find admin"
        ];
    }

    $adminResult = $adminStmt->get_result();

    if ($adminResult->num_rows === 0) {
        $adminStmt->close();

        return [
            "error" => "Admin not found"
        ];
    }

    $admin = $adminResult->fetch_assoc();

    $createdBy = $admin["id"];
    $lastUpdateBy = $admin["id"];

    $adminStmt->close();

    $createdOn = date("Y-m-d");
    $lastUpdateOn = date("Y-m-d");






    $sql = "INSERT INTO events (name, description, client, location, date, requestTitle, createdOn, createdBy, lastUpdateOn, lastUpdateBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);
        return [
            "error" => "Failed to prepare database query"
        ];
    }

    $stmt->bind_param(
        "sssssssisi",
        $name,
        $description,
        $client,
        $location,
        $date,
        $requestTitle,
        $createdOn,
        $createdBy,
        $lastUpdateOn,
        $lastUpdateBy,
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

function updateEvent(
    $eventId,
    $name,
    $description,
    $client,
    $location,
    $date,
    // $requestTitle,
    $uid
) {
    global $conn;


    // ---------------------------------------------------------
    // Get admin database ID using Firebase UID
    // ---------------------------------------------------------

    $adminSql = "
        SELECT id
        FROM admins
        WHERE fbuid = ?
        LIMIT 1
    ";


    $adminStmt = $conn->prepare($adminSql);


    if (!$adminStmt) {

        error_log($conn->error);

        return [
            "error" => "Failed to prepare admin query"
        ];
    }


    $adminStmt->bind_param(
        "s",
        $uid
    );


    if (!$adminStmt->execute()) {

        error_log($adminStmt->error);

        $adminStmt->close();

        return [
            "error" => "Failed to find admin"
        ];
    }


    $adminResult =
        $adminStmt->get_result();


    if ($adminResult->num_rows === 0) {

        $adminStmt->close();

        return [
            "error" => "Admin not found"
        ];
    }


    $admin =
        $adminResult->fetch_assoc();


    $lastUpdateBy =
        $admin["id"];


    $adminStmt->close();


    // ---------------------------------------------------------
    // Last update date
    // ---------------------------------------------------------

    $lastUpdateOn =
        date("Y-m-d");


    // ---------------------------------------------------------
    // Update event
    // ---------------------------------------------------------

    $sql = "
        UPDATE events
        SET
            name = ?,
            description = ?,
            client = ?,
            location = ?,
            date = ?,
            lastUpdateOn = ?,
            lastUpdateBy = ?
        WHERE id = ?
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        error_log($conn->error);

        return [
            "error" => "Failed to prepare update query"
        ];
    }


    $stmt->bind_param(
        "ssssssii",
        $name,
        $description,
        $client,
        $location,
        $date,
        $lastUpdateOn,
        $lastUpdateBy,
        $eventId
    );


    if (!$stmt->execute()) {

        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to update event"
        ];
    }


    $stmt->close();


    return [
        "id" => $eventId,

        "lastUpdateBy" => $lastUpdateBy,

        "lastUpdateOn" => $lastUpdateOn
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