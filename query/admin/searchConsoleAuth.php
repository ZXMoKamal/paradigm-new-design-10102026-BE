<?php

//-    // include("../../configs/header.php");
    // require_once("../../controllers/auth/auth.php");

    // // ---------------------------------------------------------
    // // 1. Verify Firebase authentication
    // // ---------------------------------------------------------

    // $user = verifyFirebaseToken();

    // // ---------------------------------------------------------
    // // 2. Check admin
    // // ---------------------------------------------------------

    // if ($user["admin"] !== true) {

    //     http_response_code(403);

    //     echo json_encode([
    //         "error" => "Admin access required"
    //     ]);

    //     exit;
    // }

    // // ---------------------------------------------
    // // Start Google OAuth
    // // ---------------------------------------------

    // $config = require __DIR__ . "/../../../../../../configs/paradigm-config.php";

    // $params = [
    //     "client_id" => $config["googleCloudConsole"]["GOOGLE_CLIENT_ID"],
    //     "redirect_uri" => $config["googleCloudConsole"]["GOOGLE_REDIRECT_URI"],
    //     "response_type" => "code",
    //     "scope" => $config["googleCloudConsole"]["GOOGLE_SEARCH_CONSOLE_SCOPE"],
    //     "access_type" => "offline",
    //     "prompt" => "consent"
    // ];

    // $authUrl = $config["googleCloudConsole"]["GOOGLE_AUTH_URL"]. '?' . http_build_query($params);

    // header("Location: " . $authUrl);

//-    // exit;


    include("../../configs/header.php");
    require_once("../../controllers/auth/auth.php");


    // ---------------------------------------------------------
    // 1. Verify Firebase authentication
    // ---------------------------------------------------------

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
        $ACLresult= checkAdminACL($admin["id"],"googleCloud","view");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have view access for google cloud"
            ]);

            exit;

        }
    // }



    // ---------------------------------------------------------
    // 2. Check admin
    // ---------------------------------------------------------

    if ($user["admin"] !== true) {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "error" => "Admin access required"
        ]);

        exit;
    }


    // ---------------------------------------------------------
    // 3. Google configuration
    // ---------------------------------------------------------

    $config = require __DIR__ . "/../../../../../../configs/paradigm-config.php";

    $google = $config["googleCloudConsole"];


    // ---------------------------------------------------------
    // 4. Generate OAuth state
    // ---------------------------------------------------------

    $state = bin2hex(random_bytes(32));


    // ---------------------------------------------------------
    // 5. Save state temporarily
    // ---------------------------------------------------------

    /*
    * For now, you can store this in PHP session.
    *
    * We will improve this later if your PHP API is stateless.
    */

    session_start();

    $_SESSION["google_oauth_state"] = $state;


    // ---------------------------------------------------------
    // 6. Build Google OAuth URL
    // ---------------------------------------------------------

    $params = [

        "client_id" =>
            $google["GOOGLE_CLIENT_ID"],

        "redirect_uri" =>
            $google["GOOGLE_REDIRECT_URI"],

        "response_type" =>
            "code",

        "scope" =>
            $google["GOOGLE_SEARCH_CONSOLE_SCOPE"],

        "access_type" =>
            "offline",

        "prompt" =>
            "consent",

        "state" =>
            $state
    ];


    $authUrl =
        $google["GOOGLE_AUTH_URL"] .
        "?" .
        http_build_query($params);


    // ---------------------------------------------------------
    // 7. Return URL to Next.js
    // ---------------------------------------------------------

    echo json_encode([
        "success" => true,
        "authUrl" => $authUrl
    ]);

    exit;