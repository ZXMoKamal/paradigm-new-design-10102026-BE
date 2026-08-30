<?php

require_once __DIR__ . "/../../configs/firebase.php";

use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

function verifyFirebaseToken() {

    global $auth;

    $headers = getallheaders();

    $authorization = $headers["Authorization"] ?? "";

    if (!$authorization) {

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

        /*
        |--------------------------------------------------------------------------
        | Get Firebase UID
        |--------------------------------------------------------------------------
        */

        $uid = $verifiedToken->claims()->get("sub");

        return [
            "uid" => $uid,
            "token" => $verifiedToken
        ];

    } catch (FailedToVerifyToken $e) {

        error_log("Firebase token verification failed: " . $e->getMessage());

        http_response_code(401);

        echo json_encode([
            "error" => "Invalid or expired authentication token"
        ]);

        exit;
    }
}