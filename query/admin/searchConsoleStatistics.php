<?php

include("../../configs/header.php");

require_once("../../controllers/auth/auth.php");

require_once(__DIR__ . "/../../controllers/googleSearchConsole/statistics.php");



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
        $ACLresult= checkAdminACL($admin["id"],"dashboard","view");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have view access for statistics"
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
    // 3. Google search console statistics
    // ---------------------------------------------------------


try {

    $siteUrl = "https://yiddish-amicably-frostlike.ngrok-free.dev/";

    $data = getSearchConsoleData(
        $siteUrl,
        "2026-09-20",
        "2026-09-25",
        ["date"],
        1000
    );

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}