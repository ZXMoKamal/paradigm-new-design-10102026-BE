<?php

$config = require __DIR__ . "/../../../../../../configs/paradigm-config.php";

if (!isset($_GET["code"])) {

    http_response_code(400);

    echo "Authorization code missing.";

    exit;
}

$code = $_GET["code"];

$postData = [
    "code" => $code,
    "client_id" => $config["googleCloudConsole"]["GOOGLE_CLIENT_ID"],
    "client_secret" =>  $config["googleCloudConsole"]["GOOGLE_CLIENT_SECRET"],
    "redirect_uri" => $config["googleCloudConsole"]["GOOGLE_REDIRECT_URI"],
    "grant_type" => "authorization_code"
];

$ch = curl_init($config["googleCloudConsole"]["GOOGLE_TOKEN_URL"]);

curl_setopt_array($ch, [
    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS => http_build_query($postData),

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded"
    ]
]);

$response = curl_exec($ch);

if ($response === false) {

    http_response_code(500);

    echo "cURL error: " . curl_error($ch);

    curl_close($ch);

    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$data = json_decode($response, true);

if ($httpCode !== 200) {

    http_response_code($httpCode);

    echo "<pre>";
    print_r($data);
    echo "</pre>";

    exit;
}

echo "<pre>";
print_r($data);
echo "</pre>";