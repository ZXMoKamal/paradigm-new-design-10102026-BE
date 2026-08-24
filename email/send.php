<?php
$config = require __DIR__ . "/../../../../../configs/paradigm-config.php";
function sendBrevoEmail(
    $toEmail,
    $toName,
    $subject,
    $htmlContent,
    $replyToEmail = null,
    $replyToName = null
) {
global $config;

    $brevoApiKey = $config["brevo"]["api_key"];

    $senderEmail = "mohamed.kamal.paradigm@gmail.com";//"info@paradigm-eg.com";
    $senderName  = "Paradigm";

    $data = [
        "sender" => [
            "name" => $senderName,
            "email" => $senderEmail
        ],

        "to" => [
            [
                "email" => $toEmail,
                "name" => $toName
            ]
        ],

        "subject" => $subject,

        "htmlContent" => $htmlContent
    ];

    // Add Reply-To only when provided
    if ($replyToEmail) {
        $data["replyTo"] = [
            "email" => $replyToEmail,
            "name" => $replyToName ?? ""
        ];
    }

    $ch = curl_init("https://api.brevo.com/v3/smtp/email");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,

        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "api-key: " . $brevoApiKey,
            "content-type: application/json"
        ],

        CURLOPT_POSTFIELDS => json_encode($data),

        CURLOPT_TIMEOUT => 15
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $curlError = curl_error($ch);

    curl_close($ch);

    if ($curlError) {
        return [
            "success" => false,
            "error" => $curlError
        ];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        return [
            "success" => false,
            "status" => $httpCode,
            "error" => json_decode($response, true) ?? $response
        ];
    }

    return [
        "success" => true,
        "response" => json_decode($response, true)
    ];
}

/*
ldjson
route request
email service id in email
*/