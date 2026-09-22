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
        $ACLresult= checkAdminACL($admin["id"],"manageAdmins","create");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have create access for admins"
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


// Only POST is allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}


// Get input
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$name = trim($_POST["name"] ?? "");


// Get permissions
$permissions = json_decode(
    $_POST["permissions"] ?? "{}",
    true
);


// Validate input
if ($email === "" || $password === "" || $name === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Email, password and name are required"
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


// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid email address"
    ]);

    exit;
}


// Validate password
if (strlen($password) < 6) {

    http_response_code(400);

    echo json_encode([
        "error" => "Password must be at least 6 characters"
    ]);

    exit;
}


try {

    /*
    |--------------------------------------------------------------------------
    | Create Firebase user
    |--------------------------------------------------------------------------
    */

    $createdUser = $auth->createUser([
        "email" => $email,
        "password" => $password,
        "displayName" => $name
    ]);


    // Firebase UID
    $uid = $createdUser->uid;


    /*
    |--------------------------------------------------------------------------
    | Set Firebase admin claim
    |--------------------------------------------------------------------------
    */

    $auth->setCustomUserClaims($uid, [
        "admin" => true
    ]);



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



    $createdBy = $admin["id"];

    $createdOn = date("Y-m-d");

    /*
    |--------------------------------------------------------------------------
    | Save admin in database
    |--------------------------------------------------------------------------
    */

    $sql = "INSERT INTO admins (fbuid, name, createdBy, createdOn)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare database query");
    }

    $stmt->bind_param("ssis", $uid, $name, $createdBy, $createdOn);

    if (!$stmt->execute()) {
        throw new Exception("Failed to save admin");
    }


    // Get the newly created admin ID
    $adminId = $conn->insert_id;


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Save admin permissions
    |--------------------------------------------------------------------------
    */

    $sqlACL = "INSERT INTO adminACL
               (adminId, module, _view, _edit, _create, _delete)
               VALUES (?, ?, ?, ?, ?, ?)";

    $stmtACL = $conn->prepare($sqlACL);

    if (!$stmtACL) {
        throw new Exception("Failed to prepare ACL query");
    }


    foreach ($permissions as $module => $actions) {

        // Make sure actions is an array
        if (!is_array($actions)) {
            continue;
        }


        // Convert true/false to 1/0
        $view = !empty($actions["view"]) ? 1 : 0;
        $edit = !empty($actions["edit"]) ? 1 : 0;
        $create = !empty($actions["create"]) ? 1 : 0;
        $delete = !empty($actions["delete"]) ? 1 : 0;


        $stmtACL->bind_param(
            "isiiii",
            $adminId,
            $module,
            $view,
            $edit,
            $create,
            $delete
        );


        if (!$stmtACL->execute()) {
            throw new Exception(
                "Failed to save permission for module: " . $module
            );
        }
    }


    $stmtACL->close();


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Admin account created successfully",
        "admin" => [
            "id" => $adminId,
            "uid" => $uid,
            "email" => $email,
            "name" => $name
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


} catch (\Throwable $e) {

    error_log($e->getMessage()); 

    http_response_code(500);

    echo json_encode([
        "error" => "Failed to create admin account"
    ]);
}