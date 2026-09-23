<?php

    $config = require __DIR__ . "/../../../../../../configs/paradigm-config.php";

    $params = [
        "client_id" => $config["googleCloudConsole"]["GOOGLE_CLIENT_ID"],
        "redirect_uri" => $config["googleCloudConsole"]["GOOGLE_REDIRECT_URI"],
        "response_type" => "code",
        "scope" => $config["googleCloudConsole"]["GOOGLE_SEARCH_CONSOLE_SCOPE"],
        "access_type" => "offline",
        "prompt" => "consent"
    ];

    $authUrl = GOOGLE_AUTH_URL . '?' . http_build_query($params);

    header("Location: " . $authUrl);

    exit;