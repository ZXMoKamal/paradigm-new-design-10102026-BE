<?php

require_once __DIR__ . "/../../configs/connectDB.php";

$config = require __DIR__ . "/../../../../../../configs/paradigm-config.php";


function getGoogleAccessToken()
{
    global $conn, $config;


    // ---------------------------------------------
    // Get stored refresh token
    // ---------------------------------------------

    $sql = "
        SELECT refresh_token
        FROM google_search_console_auth
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);


    if (!$result) {

        throw new Exception(
            "Failed to read Google authentication: " .
            mysqli_error($conn)
        );
    }


    $row = mysqli_fetch_assoc($result);


    if (!$row || empty($row["refresh_token"])) {

        throw new Exception(
            "Google Search Console is not connected."
        );
    }


    $refreshToken = $row["refresh_token"];


    // ---------------------------------------------
    // Request new access token
    // ---------------------------------------------

    $postData = [

        "client_id" => $config["googleCloudConsole"]["GOOGLE_CLIENT_ID"],

        "client_secret" => $config["googleCloudConsole"]["GOOGLE_CLIENT_SECRET"],

        "refresh_token" => $refreshToken,

        "grant_type" => "refresh_token"
    ];


    $ch = curl_init($config["googleCloudConsole"]["GOOGLE_TOKEN_URL"]);


    curl_setopt_array($ch, [

        CURLOPT_POST => true,

        CURLOPT_POSTFIELDS =>
            http_build_query($postData),

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [
            "Content-Type: application/x-www-form-urlencoded"
        ]
    ]);


    $response = curl_exec($ch);


    if ($response === false) {

        $error = curl_error($ch);

        curl_close($ch);

        throw new Exception(
            "Google token request failed: " . $error
        );
    }


    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


    curl_close($ch);


    $data = json_decode(
        $response,
        true
    );


    if ($httpCode !== 200) {

        throw new Exception(
            "Google token request failed: " .
            json_encode($data)
        );
    }


    if (
        !isset($data["access_token"]) ||
        empty($data["access_token"])
    ) {

        throw new Exception(
            "Google did not return an access token."
        );
    }


    return $data["access_token"];
}