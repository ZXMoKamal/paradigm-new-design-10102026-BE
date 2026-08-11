<?php

    include(__DIR__ . "/rules.php");

    function verifyContactUs($name, $email, $phone, $subject, $service, $message, $terms, $captcha){

        global $rules;

        $errors = [];
    
        // 🧠 helper
        function length($value) {
            return mb_strlen($value);
        }
    
        // ✅ NAME
        if (empty($name)) {
            $errors["name"] = "Name is required";
        } elseif (length($name) < $rules["name"]["min"]) {
            $errors["name"] = "Name must be at least {$rules["name"]["min"]} characters";
        } elseif (length($name) > $rules["name"]["max"]) {
            $errors["name"] = "Name must be less than {$rules["name"]["max"]} characters";
        }
    
        // ✅ EMAIL
        if (empty($email)) {
            $errors["email"] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "Invalid email";
        } elseif (length($email) < $rules["email"]["min"]) {
            $errors["email"] = "Email is too short";
        } elseif (length($email) > $rules["email"]["max"]) {
            $errors["email"] = "Email is too long";
        }
    
        // ✅ PHONE
        if (empty($phone)) {
            $errors["phone"] = "Phone is required";
        } elseif (length($phone) < $rules["phone"]["min"]) {
            $errors["phone"] = "Phone must be at least {$rules["phone"]["min"]} digits";
        } elseif (length($phone) > $rules["phone"]["max"]) {
            $errors["phone"] = "Phone must be less than {$rules["phone"]["max"]} digits";
        }
    
        // ✅ SERVICE
        if (empty($service)) {
            $errors["service"] = "Please select a service";
        }
    
        // ✅ SUBJECT
        if (empty($subject)) {
            $errors["subject"] = "Subject is required";
        } elseif (length($subject) < $rules["subject"]["min"]) {
            $errors["subject"] = "Subject must be at least {$rules["subject"]["min"]} characters";
        } elseif (length($subject) > $rules["subject"]["max"]) {
            $errors["subject"] = "Subject must be less than {$rules["subject"]["max"]} characters";
        }
    
        // ✅ MESSAGE
        if (empty($message)) {
            $errors["message"] = "Message is required";
        } elseif (length($message) < $rules["message"]["min"]) {
            $errors["message"] = "Message must be at least {$rules["message"]["min"]} characters";
        } elseif (length($message) > $rules["message"]["max"]) {
            $errors["message"] = "Message must be less than {$rules["message"]["max"]} characters";
        }
    
        // ✅ TERMS
        if (!$terms || $terms === "false" || $terms === 0) {
            $errors["terms"] = "You must accept terms";
        }
    
        // ✅ CAPTCHA
        if (empty($captcha)) {
            $errors["token"] = "Please complete the captcha";
        }
    
        // 🚨 If any errors → return ALL (like Yup)

        $state = false;

        if (empty($errors)) {
            $state = true;
        }

        return ["state"=>$state,"errors" => $errors];
    }