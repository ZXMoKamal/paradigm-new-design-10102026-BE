<?php
    include(__DIR__."/../../configs/connectDB.php");
    include(__DIR__."/../../enc/enc_dec.php");
    include(__DIR__."/services.php");

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



 function getAllContactUs($from, $to)
{
    global $conn;

    // Validate pagination values
    $from = max(0, (int)$from);
    $to   = max($from, (int)$to);

    $limit = $to - $from;

    if ($limit <= 0) {
        return [
            "error" => "Invalid pagination range"
        ];
    }

    $sql = "SELECT 
                id,
                name,
                email,
                phone,
                subject,
                service,
                serviceName,
                message,
                adminMailStatus,
                customerMailStatus
            FROM contactUs
            ORDER BY id DESC
            LIMIT ?, ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return [
            "error" => "Failed to prepare query"
        ];
    }

    $stmt->bind_param("ii", $from, $limit);

    if (!$stmt->execute()) {
        return [
            "error" => "Failed to execute query"
        ];
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        return [
            "error" => "No contact messages found"
        ];
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    // Decode/decrypt contact data + get service name


    foreach ($data as &$row) {

        $row["name"] = decryptData($row["name"]);
        $row["email"] = decryptData($row["email"]);
        $row["phone"] = decryptData($row["phone"]);
        $row["service"] = decryptData($row["service"]);
        $row["serviceName"] = decryptData($row["serviceName"]);
        $row["subject"] = decryptData($row["subject"]);
        $row["message"] = decryptData($row["message"]);
    }

    unset($row);

    return $data;
}