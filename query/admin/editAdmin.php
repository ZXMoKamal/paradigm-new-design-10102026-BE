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
        $ACLresult= checkAdminACL($admin["id"],"manageAdmins","edit");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have edit access for admins"
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


// Only PUT is allowed
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}


// Get request body
$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid request data"
    ]);

    exit;
}


// Get target admin UID
$uid = trim($data["uid"] ?? "");

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");

$permissions = $data["permissions"] ?? [];


// Validate UID
if ($uid === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Admin UID is required"
    ]);

    exit;
}


// Validate name
if ($name === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Admin name is required"
    ]);

    exit;
}


// Validate email
if ($email === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Admin email is required"
    ]);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid email address"
    ]);

    exit;
}


// Validate permissions
if (!is_array($permissions)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid permissions data"
    ]);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | The logged-in user must be Super Admin
    |--------------------------------------------------------------------------
    */

    // if (!$user["SupAdmin"]) {

    //     http_response_code(403);

    //     echo json_encode([
    //         "error" => "Only Super Admin can edit administrators"
    //     ]);

    //     exit;
    // }


    /*
    |--------------------------------------------------------------------------
    | Get target admin
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT id, fbuid, name, SupAdmin
            FROM admins
            WHERE fbuid = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare admin query");
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


    $adminId = (int)$admin["id"];


    /*
    |--------------------------------------------------------------------------
    | Prevent editing yourself
    |--------------------------------------------------------------------------
    */

    if ($uid === $user["uid"]) {

        http_response_code(400);

        echo json_encode([
            "error" => "You cannot edit your own admin account"
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent editing another Super Admin
    |--------------------------------------------------------------------------
    */

    if ((int)$admin["SupAdmin"] === 1) {

        http_response_code(403);

        echo json_encode([
            "error" => "Super Admin cannot be edited"
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Firebase email
    |--------------------------------------------------------------------------
    */

    $auth->updateUser($uid, [
        "email" => $email,
        "displayName" => $name
    ]);



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
        $user["uid"]
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



    /*
    |--------------------------------------------------------------------------
    | Update admin name in database
    |--------------------------------------------------------------------------
    */



$lastUpdateOn = date("Y-m-d");

    $sql = "UPDATE admins
            SET name = ?,
            lastUpdateOn = ?,
            lastUpdateBy = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare admin update query");
    }

    $stmt->bind_param(
        "ssii",
        $name,
                $lastUpdateOn,
        $lastUpdateBy,
        $adminId

    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to update admin");
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Update ACL
    |--------------------------------------------------------------------------
    */

    // Remove old permissions
    $sql = "DELETE FROM adminACL
            WHERE adminId = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare ACL delete query");
    }

    $stmt->bind_param("i", $adminId);

    if (!$stmt->execute()) {
        throw new Exception("Failed to remove old permissions");
    }

    $stmt->close();


    // Insert new permissions
    $sql = "INSERT INTO adminACL
            (
                adminId,
                module,
                _view,
                _edit,
                _create,
                _delete
            )
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare ACL insert query");
    }


    foreach ($permissions as $module => $actions) {

        if (!is_array($actions)) {
            continue;
        }


        $view = !empty($actions["view"]) ? 1 : 0;
        $edit = !empty($actions["edit"]) ? 1 : 0;
        $create = !empty($actions["create"]) ? 1 : 0;
        $delete = !empty($actions["delete"]) ? 1 : 0;


        $stmt->bind_param(
            "isiiii",
            $adminId,
            $module,
            $view,
            $edit,
            $create,
            $delete
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "Failed to save permission for module: " . $module
            );
        }
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Admin updated successfully",
        "admin" => [
            "id" => $adminId,
            "uid" => $uid,
            "name" => $name,
            "email" => $email
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


} catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {

    http_response_code(404);

    echo json_encode([
        "error" => "Firebase admin account not found"
    ]);


} catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {

    http_response_code(409);

    echo json_encode([
        "error" => "This email is already used by another account"
    ]);


} catch (\Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500); 

    echo json_encode([
        "error" => "Failed to update admin"
    ]);
}