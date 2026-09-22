<?php

include("../../configs/header.php");

require_once("../../controllers/auth/auth.php");
require_once("../../controllers/db/ACL.php");


/*
|--------------------------------------------------------------------------
| Verify Firebase token
|--------------------------------------------------------------------------
*/

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



/*
|--------------------------------------------------------------------------
| Check Admin
|--------------------------------------------------------------------------
*/

if (!$user["admin"]) {

    http_response_code(403);

    echo json_encode([
        "error" => "Admin access required"
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get current admin DB ID
|--------------------------------------------------------------------------
*/

$adminId = $user["id"];


/*
|--------------------------------------------------------------------------
| Get ACL
|--------------------------------------------------------------------------
*/

try {

    $permissions = getAdminACL($adminId);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([

        "success" => true,

        "adminId" => $adminId,

        "permissions" => $permissions

    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Failed to get admin permissions"
    ]);
}