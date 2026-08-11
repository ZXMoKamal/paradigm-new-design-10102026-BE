<?php
    include("../configs/connectDB.php");

    function setContactUs( $name, $email, $phone, $subject, $service, $message) {
        global $conn;

        // ✅ Insert into DB
        $sql = "INSERT INTO contactUs (name, email, phone, subject, service, message) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $service, $message);

        if ($stmt->execute()) {
            $state = true;
        } else {
            $state = false;
        }

        $stmt->close();
        $conn->close();

        return $state;
    }