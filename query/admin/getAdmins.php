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


try {

    $sql = "SELECT id, fbuid, name, createdOn, createdBy
            FROM admins
            ORDER BY id DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Failed to get admins");
    }

    $admins = [];

    while ($row = $result->fetch_assoc()) {

        try {

            // Get Firebase user
            $firebaseUser = $auth->getUser($row["fbuid"]);

            $admins[] = [
                "id" => $row["id"],
                "uid" => $row["fbuid"],
                "name" => $row["name"],
                "email" => $firebaseUser->email,
                "createdOn" => $row["createdOn"],
                "createdBy" => getUserNamefromDB($row["createdBy"])
            ];

        } catch (\Throwable $e) {

            // Firebase user may have been deleted
            $admins[] = [
                "id" => $row["id"],
                "uid" => $row["fbuid"],
                "name" => $row["name"],
                "email" => null,
                "createdOn" => $row["createdOn"],
                "createdBy" => getUserNamefromDB($row["createdBy"])
            ];
        }
    }


    http_response_code(200);

    echo json_encode(
        $admins,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );


} catch (\Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Failed to get admins"
    ]);
}

