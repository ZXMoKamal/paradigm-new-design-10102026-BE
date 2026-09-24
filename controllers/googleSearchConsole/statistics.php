<?php

require_once("../../configs/google.php");
require_once("./googleToken.php");

function getSearchConsoleData(
    $siteUrl,
    $startDate,
    $endDate,
    $dimensions = ["date"],
    $rowLimit = 1000
) {
    /*
     * Get a fresh Google access token.
     */
    $accessToken = getGoogleAccessToken();

    /*
     * Search Console API URL.
     *
     * The site URL must be URL encoded.
     */
    $url =
        "https://www.googleapis.com/webmasters/v3/sites/" .
        rawurlencode($siteUrl) .
        "/searchAnalytics/query";

    /*
     * Request body.
     */
    $body = [
        "startDate" => $startDate,
        "endDate" => $endDate,
        "dimensions" => $dimensions,
        "rowLimit" => $rowLimit
    ];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POSTFIELDS => json_encode($body),

        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $accessToken,
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($ch);

    if ($response === false) {

        $error = curl_error($ch);

        curl_close($ch);

        throw new Exception(
            "Search Console request failed: " . $error
        );
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode !== 200) {

        throw new Exception(
            "Search Console API error: " .
            json_encode($data)
        );
    }

    return $data;
}