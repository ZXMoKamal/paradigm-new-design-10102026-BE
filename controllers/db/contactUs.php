<?php
    include("../configs/connectDB.php");
    include("../enc/enc_dec.php");

    function setContactUs( $name, $email, $phone, $subject, $service, $serviceName, $message, $adminMailStatus, $customerMailStatus) {
        global $conn;

        $nameEncrypted = encryptData($name);
        $emailEncrypted = encryptData($email);
        $phoneEncrypted = encryptData($phone);
        $subjectEncrypted = encryptData($subject);
        $messageEncrypted = encryptData($message);
        $serviceEncrypted = encryptData($service);
        $serviceNameEncrypted = encryptData($serviceName);



        // ✅ Insert into DB
        $sql = "INSERT INTO contactUs (name, email, phone, subject, service, serviceName, message, adminMailStatus, customerMailStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssii", $nameEncrypted, $emailEncrypted, $phoneEncrypted, $subjectEncrypted, $serviceEncrypted, $serviceNameEncrypted, $messageEncrypted, $adminMailStatus, $customerMailStatus);

        if ($stmt->execute()) {
            $state = true;
        } else {
            $state = false;
        }

        $stmt->close();
        $conn->close();

        return $state;
    }