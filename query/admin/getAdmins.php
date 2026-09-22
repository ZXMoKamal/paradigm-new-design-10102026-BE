<?php

include("../../configs/header.php");

require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/contactUs.php");
require_once("../../configs/firebase.php");
require_once("../../configs/connectDB.php");
require_once("../../controllers/db/getAdmins.php");


// Verify Firebase token
$user = verifyFirebaseToken();



    // if($isAdmin){
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
            $user["uid"]
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
        $ACLresult= checkAdminACL($admin["id"],"manageAdmins","view");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have view access for admins"
            ]);

            exit;

        }
    // }



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
global $user;
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
                createdBy,
                lastUpdateOn,
                lastUpdateBy
            FROM admins
            ORDER BY id DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Failed to get admins");
    }


    $admins = [];


    while ($row = $result->fetch_assoc()) {
global $user;

        $adminId = (int)$row["id"];


        /*
        |--------------------------------------------------------------------------
        | Get ACL
        |--------------------------------------------------------------------------
        */

        $permissions = getAdminPermissions($adminId);


        try {
global $user;

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
                    $row["createdBy"],$user["uid"]
                ),
                "lastUpdateOn" => $row["lastUpdateOn"],

                "lastUpdateBy" => getUserNamefromDB(
                    $row["lastUpdateBy"],$user["uid"]
                ),

                /*
                | ACL permissions
                */
                "permissions" => $permissions
            ];


        } catch (\Throwable $e) {
global $user;

            // Firebase user may have been deleted

echo $e->getMessage();
            $admins[] = [

                "id" => $adminId,

                "uid" => $row["fbuid"],

                "name" => $row["name"],

                "email" => null,

                "SupAdmin" => (bool)$row["SupAdmin"],

                "createdOn" => $row["createdOn"],

                "createdBy" => getUserNamefromDB(
                    $row["createdBy"],$user["uid"]
                ),
                "lastUpdateOn" => $row["lastUpdateOn"],

                "lastUpdateBy" => getUserNamefromDB(
                    $row["lastUpdateBy"],$user["uid"]
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