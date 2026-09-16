<?php

require_once(__DIR__ . "/../../configs/connectDB.php");


function checkAdminACL($adminId, $module, $access)
{
    global $conn;


    /*
    |--------------------------------------------------------------------------
    | Validate admin ID
    |--------------------------------------------------------------------------
    */

    $adminId = (int)$adminId;

    if ($adminId <= 0) {

        return [
            "success" => false,
            "error" => "Invalid admin ID"
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Validate module
    |--------------------------------------------------------------------------
    */

    $module = trim($module);

    if ($module === "") {

        return [
            "success" => false,
            "error" => "Invalid module"
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed access rights
    |--------------------------------------------------------------------------
    */

    $allowedAccess = [
        "view",
        "create",
        "edit",
        "delete"
    ];


    $access = strtolower(trim($access));


    if (!in_array($access, $allowedAccess, true)) {

        return [
            "success" => false,
            "error" => "Invalid access right"
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Convert access right to database column
    |--------------------------------------------------------------------------
    */

    $accessColumns = [
        "view" => "_view",
        "create" => "_create",
        "edit" => "_edit",
        "delete" => "_delete"
    ];


    $column = $accessColumns[$access];


    /*
    |--------------------------------------------------------------------------
    | Check permission
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT `$column`
            FROM adminACL
            WHERE adminId = ?
            AND module = ?
            LIMIT 1";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        error_log($conn->error);

        return [
            "success" => false,
            "error" => "Failed to prepare ACL query"
        ];
    }


    $stmt->bind_param(
        "is",
        $adminId,
        $module
    );


    if (!$stmt->execute()) {

        error_log($stmt->error);

        $stmt->close();

        return [
            "success" => false,
            "error" => "Failed to check ACL permission"
        ];
    }


    $result = $stmt->get_result();


    /*
    |--------------------------------------------------------------------------
    | No ACL record
    |--------------------------------------------------------------------------
    */

    if (!$result || $result->num_rows === 0) {

        $stmt->close();

        return [
            "success" => false,
            "allowed" => false
        ];
    }


    $row = $result->fetch_assoc();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Return result
    |--------------------------------------------------------------------------
    */

    $allowed = (int)$row[$column] === 1;


    return [
        "success" => true,
        "allowed" => $allowed
    ];
}


function getAdminACL($adminId)
{
    global $conn;


    /*
    |--------------------------------------------------------------------------
    | Validate Admin ID
    |--------------------------------------------------------------------------
    */

    $adminId = (int)$adminId;

    if ($adminId <= 0) {

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Admin ACL
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT
                module,
                _view,
                _edit,
                _create,
                _delete
            FROM adminACL
            WHERE adminId = ?";


    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            "Failed to prepare ACL query"
        );
    }


    $stmt->bind_param(
        "i",
        $adminId
    );


    if (!$stmt->execute()) {

        $stmt->close();

        throw new Exception(
            "Failed to execute ACL query"
        );
    }


    $result = $stmt->get_result();

    $permissions = [];


    while ($row = $result->fetch_assoc()) {

        $permissions[$row["module"]] = [

            "view" => (bool)$row["_view"],

            "edit" => (bool)$row["_edit"],

            "create" => (bool)$row["_create"],

            "delete" => (bool)$row["_delete"]
        ];
    }


    $stmt->close();


    return $permissions;
}