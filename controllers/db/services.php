<?php
include(__DIR__."/../../configs/connectDB.php");
    require_once(__DIR__."/getAdmins.php");
    
function getAllServices($isAdmin = false)
{
    global $conn;

    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "SELECT 
                    id,
                    title,
                    description,
                    icon_code,
                    requestTitle
                FROM services";

    }


    /*
     * Admin users
     */
    else {

        $sql = "SELECT 
                    id,
                    title,
                    description,
                    icon_code,
                    requestTitle,
                    createdOn,
                    createdBy
                FROM services";
    }


    $result = $conn->query($sql);

    if (!$result || $result->num_rows == 0) {
        return ["error" => "No data found"];
    }


    $data = $result->fetch_all(MYSQLI_ASSOC);


    /*
     * Only admins can see creator information.
     */
    if ($isAdmin) {

        foreach ($data as &$service) {

            if ($service["createdBy"] !== null) {

                $service["createdBy"] = getUserNamefromDB(
                    (int)$service["createdBy"]
                );
            }
        }

        unset($service);
    }


    return $data;
}


function getService($requestTitle, $isAdmin = false)
{
    global $conn;


    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "
            SELECT 
                s.id, 
                s.title, 
                s.description,
                s.icon_code,
                s.requestTitle,
                CONCAT(
                    '[',
                    IFNULL(
                        GROUP_CONCAT(
                            CONCAT(
                                '{\"id\":', p.id, 
                                ',\"point\":\"',
                                REPLACE(p.point, '\"', '\\\\\"'),
                                '\"}'
                            )
                        ),
                        ''
                    ),
                    ']'
                ) AS points
            FROM services s
            LEFT JOIN services_points p ON p.sid = s.id
            WHERE s.requestTitle = ?
            GROUP BY s.id
        ";

    }


    /*
     * Admin users
     */
    else {

        $sql = "
            SELECT 
                s.id, 
                s.title, 
                s.description,
                s.icon_code,
                s.requestTitle,
                s.createdOn,
                s.createdBy,
                CONCAT(
                    '[',
                    IFNULL(
                        GROUP_CONCAT(
                            CONCAT(
                                '{\"id\":', p.id, 
                                ',\"point\":\"',
                                REPLACE(p.point, '\"', '\\\\\"'),
                                '\"}'
                            )
                        ),
                        ''
                    ),
                    ']'
                ) AS points
            FROM services s
            LEFT JOIN services_points p ON p.sid = s.id
            WHERE s.requestTitle = ?
            GROUP BY s.id
        ";
    }


    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $stmt->bind_param("s", $requestTitle);

    if (!$stmt->execute()) {
        $stmt->close();
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();


    if (!$result || $result->num_rows == 0) {
        $stmt->close();
        return ["error" => "No data found"];
    }


    // Fetch service
    $data = $result->fetch_assoc();


    /*
     * Convert JSON string to array
     */
    $data["points"] = json_decode($data["points"], true);


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

    return $data;
}


function getServiceById($id)
{
    global $conn;

    $sql = "SELECT id, title FROM services WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $id = (int)$id;

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        $stmt->close();
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        $stmt->close();
        return ["error" => "No data found"];
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $data;
}


function addService($title, $iconCode, $requestTitle)
{
    global $conn;

    $sql = "
        INSERT INTO services
        (title, icon_code, requestTitle)
        VALUES (?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        error_log($conn->error);

        return [
            "error" => "Failed to prepare service query"
        ];
    }

    $stmt->bind_param(
        "sss",
        $title,
        $iconCode,
        $requestTitle
    );

    if (!$stmt->execute()) {

        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to create service"
        ];
    }

    $serviceId = $conn->insert_id;

    $stmt->close();

    return [
        "id" => $serviceId
    ];
}


function addServiceHighlights($serviceId, $highlights)
{
    global $conn;

    $sql = "INSERT INTO services_points (sid, point) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        error_log($conn->error);

        return [
            "error" => "Failed to prepare highlight query"
        ];
    }

    foreach ($highlights as $point) {

        $point = trim($point);

        // Don't save empty highlights
        if ($point === "") {
            continue;
        }

        $stmt->bind_param(
            "is",
            $serviceId,
            $point
        );

        if (!$stmt->execute()) {

            error_log($stmt->error);

            $stmt->close();

            return [
                "error" => "Failed to save service highlight"
            ];
        }
    }

    $stmt->close();

    return [
        "success" => true
    ];
}



function deleteService($id)
{
    global $conn;

    $id = (int)$id;

    if ($id <= 0) {
        return [
            "error" => "Invalid service ID"
        ];
    }

    // ---------------------------------------------------------
    // 1. Check if service exists
    // ---------------------------------------------------------

    $sql = "SELECT id FROM services WHERE id = ?";

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
            "error" => "Service not found"
        ];
    }

    $stmt->close();


    // ---------------------------------------------------------
    // 2. Start transaction
    // ---------------------------------------------------------

    $conn->begin_transaction();

    try {

        // -----------------------------------------------------
        // 3. Delete service highlights
        // -----------------------------------------------------

        $sql = "DELETE FROM services_points WHERE sid = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Failed to prepare highlights delete query");
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to delete service highlights");
        }

        $stmt->close();


        // -----------------------------------------------------
        // 4. Delete service
        // -----------------------------------------------------

        $sql = "DELETE FROM services WHERE id = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Failed to prepare service delete query");
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to delete service");
        }

        $stmt->close();


        // -----------------------------------------------------
        // 5. Commit
        // -----------------------------------------------------

        $conn->commit();


        return [
            "success" => true,
            "id" => $id
        ];

    } catch (Exception $e) {

        // -----------------------------------------------------
        // 6. Rollback if anything failed
        // -----------------------------------------------------

        $conn->rollback();

        error_log($e->getMessage());

        return [
            "error" => "Failed to delete service"
        ];
    }
}