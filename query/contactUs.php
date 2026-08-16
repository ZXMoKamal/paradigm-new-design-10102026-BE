<?php

    include ("../configs/header.php");
    include ("../controllers/db/contactUs.php");
    include ("../forms/verifyContactUs.php");
    include ("../captcha/verifyCaptcha.php");
    include ("../email/send.php");
    include ("../email/templates.php");

    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $input = json_decode(file_get_contents("php://input"), true);

    // ✅ Get inputs
    $name     = trim($input['name'] ?? '');
    $email    = trim($input['email'] ?? '');
    $phone    = trim($input['phone'] ?? '');
    $subject  = trim($input['subject'] ?? '');
    $service  = trim($input['service'] ?? '');
    $message  = trim($input['message'] ?? '');
    $terms    = $input['terms'] ?? '';
    $captcha  = $input['token'] ?? '';

    $verifyContactUsState = verifyContactUs($name, $email, $phone, $subject, $service, $message, $terms, $captcha);

    if(!$verifyContactUsState["state"]){
        http_response_code(422); // validation error
            echo json_encode([
                "errors" => $verifyContactUsState["errors"]
            ]);
            exit;
    }

    if(!verifyCaptcha($captcha)){
        http_response_code(403);
        echo json_encode(["error" => "Captcha verification failed"]);
        exit;
    };

    // 🛡️ Optional sanitize (for output safety later)
    $name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $terms = htmlspecialchars($terms, ENT_QUOTES, 'UTF-8');
    $captcha = htmlspecialchars($captcha, ENT_QUOTES, 'UTF-8');


    $customerMail = sendBrevoEmail(
        $email,
        $name,
        "We received your message",
        customerMSGTemplate($name)
    );
    
    $adminMail = sendBrevoEmail(
        //"info@paradigm-eg.com",
        "mohamed.kamal.paradigm@gmail.com",
        "Paradigm",
        "New Contact Form Message: " . $subject,
        adminMSGTemplate($name,$email,$phone,$service,$subject,$message),
        $email,
        $name
    );

    if(!$customerMail["success"]){
        http_response_code(500);
        echo json_encode(["error" => "Failed to send email (client)"]);
        // echo $customerMail["error"];
        // exit;
    }
    
    if(!$adminMail["success"]){
        http_response_code(500);
        echo json_encode(["error" => "Failed to send email (admin)"]);
        // echo $adminMail["error"];
    // exit;
    }

    if (setContactUs($name, $email, $phone, $subject, $service, $message, $adminMail["success"], $customerMail["success"])) {
        //$encryption
        http_response_code(200);
        echo json_encode(["success" => "Form submitted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database error"]);
    }