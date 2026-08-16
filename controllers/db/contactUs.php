<?php
    include("../configs/connectDB.php");

    function setContactUs( $name, $email, $phone, $subject, $service, $message, $adminMailStatus, $customerMailStatus) {
        global $conn;

        // ✅ Insert into DB
        $sql = "INSERT INTO contactUs (name, email, phone, subject, service, message, adminMailStatus, customerMailStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssii", $name, $email, $phone, $subject, $service, $message, $adminMailStatus, $customerMailStatus);

        if ($stmt->execute()) {
            $state = true;
        } else {
            $state = false;
        }

        $stmt->close();
        $conn->close();

        return $state;
    }