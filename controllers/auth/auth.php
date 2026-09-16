<?php
// require_once __DIR__ . "/../../configs/connectDB.php";
// require_once __DIR__ . "/../../configs/firebase.php";

// use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

// function verifyFirebaseToken($required = true) {

//     global $auth;

//     $headers = getallheaders();

//     $authorization = $headers["Authorization"] ?? "";

//     if (!$authorization) {
//         if (!$required) {
//             return null;
//         }
//         http_response_code(401);

//         echo json_encode([
//             "error" => "Authentication required"
//         ]);

//         exit;
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Extract Bearer token
//     |--------------------------------------------------------------------------
//     */

//     if (!preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) {

//         http_response_code(401);

//         echo json_encode([
//             "error" => "Invalid authorization header"
//         ]);

//         exit;
//     }

//     $idToken = trim($matches[1]);

//     /*
//     |--------------------------------------------------------------------------
//     | Verify Firebase ID token
//     |--------------------------------------------------------------------------
//     */

//     try {

//         $verifiedToken = $auth->verifyIdToken($idToken);

//         // Get Firebase UID from the verified token
//         $uid = $verifiedToken->claims()->get("sub");

//         // Get admin status from Firebase Custom Claims
//         $admin = $verifiedToken->claims()->get("admin", false);

//         // Get user name from database
//         global $conn;

//         $sql = "SELECT name, SupAdmin FROM admins WHERE fbuid = ?";

//         $stmt = $conn->prepare($sql);

//         if (!$stmt) {
//             throw new Exception("Database query preparation failed");
//         }

//         $stmt->bind_param("s", $uid);
//         $stmt->execute();

//         $result = $stmt->get_result();
//         $user = $result->fetch_assoc();

//         $stmt->close();

//         if (!$user) {
//             http_response_code(404);

//             echo json_encode([
//                 "error" => "User not found"
//             ]);

//             exit;
//         }

//         $name = $user["name"];
//         $supAdmin = $user["SupAdmin"];

//         return [
//             "uid" => $uid,
//             "name" => $name,
//             "token" => $verifiedToken,
//             "supAdmin" => $supAdmin,
//             "admin" => $admin
//         ];

//     }catch (FailedToVerifyToken $e) {

//         error_log("Firebase token verification failed: " . $e->getMessage());

//         http_response_code(401);

//         echo json_encode([
//             "error" => "Invalid or expired authentication token"
//         ]);

//         exit;
//     }
// }

require_once __DIR__ . "/../../configs/connectDB.php";

require_once __DIR__ . "/../../configs/firebase.php";

use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

function verifyFirebaseToken($required = true) {

    global $auth;

    $headers = getallheaders();

    $authorization = $headers["Authorization"] ?? "";

    if (!$authorization) {

        if (!$required) {

            return null;

        }

        http_response_code(401);

        echo json_encode([
            "error" => "Authentication required"
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Extract Bearer token
    |--------------------------------------------------------------------------
    */

    if (!preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) {

        http_response_code(401);

        echo json_encode([
            "error" => "Invalid authorization header"
        ]);

        exit;
    }

    $idToken = trim($matches[1]);

    /*
    |--------------------------------------------------------------------------
    | Verify Firebase ID token
    |--------------------------------------------------------------------------
    */

    try {

        $verifiedToken = $auth->verifyIdToken($idToken);

        // Get Firebase UID from the verified token
        $uid = $verifiedToken->claims()->get("sub");

        // Get admin status from Firebase Custom Claims
        $admin = $verifiedToken->claims()->get("admin", false);

        // Get user name from database
        global $conn;

        $sql = "SELECT id, name, SupAdmin
                FROM admins
                WHERE fbuid = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            throw new Exception(
                "Database query preparation failed"
            );
        }

        $stmt->bind_param("s", $uid);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();

        if (!$user) {

            http_response_code(404);

            echo json_encode([
                "error" => "User not found"
            ]);

            exit;
        }


        $adminId = (int)$user["id"];

        $name = $user["name"];

        $supAdmin = $user["SupAdmin"];


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
                "ACL query preparation failed"
            );
        }

        $stmt->bind_param("i", $adminId);

        $stmt->execute();

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


        return [

            "id" => $adminId,

            "uid" => $uid,

            "name" => $name,

            "token" => $verifiedToken,

            "supAdmin" => $supAdmin,

            "admin" => $admin,

            "permissions" => $permissions
        ];


    } catch (FailedToVerifyToken $e) {

        error_log(
            "Firebase token verification failed: " .
            $e->getMessage()
        );

        http_response_code(401);

        echo json_encode([
            "error" => "Invalid or expired authentication token"
        ]);

        exit;
    }
}