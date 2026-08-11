<?php

    function verifyCaptcha ($captcha) {

        // 🔐 Cloudflare secret key
        $secretKey = "0x4AAAAAAEDtK1VqwFtn6qkJAY8uSsUm7v8";

        // 🔒 Verify Turnstile
        $verifyUrl = "https://challenges.cloudflare.com/turnstile/v0/siteverify";

        $data = [
            'secret' => $secretKey,
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context  = stream_context_create($options);
        $result   = file_get_contents($verifyUrl, false, $context);
        $response = json_decode($result, true);

        if ($response && $response['success']) {
            return true;
        }
        
        return false;
    }