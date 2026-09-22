<?php

include("../../configs/header.php");

require_once("../../controllers/auth/auth.php");
require_once("../../configs/firebase.php");
require_once("../../configs/connectDB.php");
require_once(__DIR__."/../../controllers/db/ACL.php");

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
        $ACLresult= checkAdminACL($admin["id"],"manageAdmins","delete");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have delete access for admins"
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


// Only DELETE is allowed
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}


// Get UID
$uid = trim($_GET["uid"] ?? "");

if ($uid === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Admin UID is required"
    ]);

    exit;
}


try {

    // Check admin exists and if he is SupAdmin
    $sql = "SELECT id, SupAdmin FROM admins WHERE fbuid = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare query");
    }

    $stmt->bind_param("s", $uid);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        $stmt->close();

        http_response_code(404);

        echo json_encode([
            "error" => "Admin not found"
        ]);

        exit;
    }

    $admin = $result->fetch_assoc();

    $stmt->close();


    // Prevent deleting SupAdmin
    if ((int)$admin["SupAdmin"] === 1) {

        http_response_code(403);

        echo json_encode([
            "error" => "Super Admin cannot be deleted"
        ]);

        exit;
    }


    // Prevent deleting yourself
    if ($uid === $user["uid"]) {

        http_response_code(400);

        echo json_encode([
            "error" => "You cannot delete your own admin account"
        ]);

        exit;
    }


    // Delete Firebase account
    $auth->deleteUser($uid);


    // Delete from database
    $sql = "DELETE FROM admins WHERE fbuid = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare delete query");
    }

    $stmt->bind_param("s", $uid);

    if (!$stmt->execute()) {
        throw new Exception("Failed to delete admin from database");
    }

    $stmt->close();


    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Admin deleted successfully"
    ]);


} catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {

    // Firebase user doesn't exist anymore.
    // Still remove the admin from our database.

    $sql = "DELETE FROM admins WHERE fbuid = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare delete query");
    }

    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $stmt->close();

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Admin deleted successfully"
    ]);


} catch (\Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Failed to delete admin"
    ]);
}