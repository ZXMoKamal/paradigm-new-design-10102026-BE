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