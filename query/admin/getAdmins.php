<?php

include("../../configs/header.php");

require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/contactUs.php");
require_once("../../configs/firebase.php");
require_once("../../configs/connectDB.php");
require_once("../../controllers/db/getAdmins.php");


// Verify Firebase token
$user = verifyFirebaseToken();


// Check admin
if (!$user["admin"]) {

    http_response_code(403);

    echo json_encode([
        "error" => "Admin access required"
    ]);

    exit;
}


// Only GET is allowed
if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get Admin Permissions
|--------------------------------------------------------------------------
*/

function getAdminPermissions($adminId)
{
    global $conn;

    $sql = "SELECT
                module,
                _view,
                _edit,
                _create,
                _delete
            FROM adminacl
            WHERE adminId = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare ACL query");
    }

    $stmt->bind_param("i", $adminId);

    if (!$stmt->execute()) {

        $stmt->close();

        throw new Exception("Failed to execute ACL query");
    }

    $result = $stmt->get_result();

    $permissions = [];

    while ($row = $result->fetch_assoc()) {

        $module = $row["module"];

        $permissions[$module] = [
            "view" => (bool)$row["_view"],
            "edit" => (bool)$row["_edit"],
            "create" => (bool)$row["_create"],
            "delete" => (bool)$row["_delete"]
        ];
    }

    $stmt->close();

    return $permissions;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Get Admins
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT
                id,
                fbuid,
                name,
                SupAdmin,
                createdOn,
                createdBy
            FROM admins
            ORDER BY id DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Failed to get admins");
    }


    $admins = [];


    while ($row = $result->fetch_assoc()) {

        $adminId = (int)$row["id"];


        /*
        |--------------------------------------------------------------------------
        | Get ACL
        |--------------------------------------------------------------------------
        */

        $permissions = getAdminPermissions($adminId);


        try {

            /*
            |--------------------------------------------------------------------------
            | Get Firebase user
            |--------------------------------------------------------------------------
            */

            $firebaseUser = $auth->getUser(
                $row["fbuid"]
            );


            $admins[] = [

                "id" => $adminId,

                "uid" => $row["fbuid"],

                "name" => $row["name"],

                "email" => $firebaseUser->email,

                "SupAdmin" => (bool)$row["SupAdmin"],

                "createdOn" => $row["createdOn"],

                "createdBy" => getUserNamefromDB(
                    $row["createdBy"]
                ),

                /*
                | ACL permissions
                */
                "permissions" => $permissions
            ];


        } catch (\Throwable $e) {

            // Firebase user may have been deleted


            $admins[] = [

                "id" => $adminId,

                "uid" => $row["fbuid"],

                "name" => $row["name"],

                "email" => null,

                "SupAdmin" => (bool)$row["SupAdmin"],

                "createdOn" => $row["createdOn"],

                "createdBy" => getUserNamefromDB(
                    $row["createdBy"]
                ),

                /*
                | ACL permissions
                */
                "permissions" => $permissions
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode(
        $admins,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


} catch (\Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Failed to get admins"
    ]);
}