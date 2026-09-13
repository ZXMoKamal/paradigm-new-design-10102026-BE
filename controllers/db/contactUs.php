<?php
    include(__DIR__."/../../configs/connectDB.php");
    include(__DIR__."/../../enc/enc_dec.php");
    include_once(__DIR__."/services.php");
    require_once(__DIR__."/getAdmins.php");

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
        $sql = "INSERT INTO contactUs (name, email, phone, subject, service, serviceName, message, adminMailStatus, customerMailStatus, createdOn, createdBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssiisi", $nameEncrypted, $emailEncrypted, $phoneEncrypted, $subjectEncrypted, $serviceEncrypted, $serviceNameEncrypted, $messageEncrypted, $adminMailStatus, $customerMailStatus, date("Y-m-d"), 88);

        if ($stmt->execute()) {
            $state = true;
        } else {
            $state = false;
        }

        $stmt->close();
        $conn->close();

        return $state;
    }



function getAllContactUs($from, $to, $search = null)
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
                customerMailStatus,
                createdOn,
                createdBy
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

    // Decode/decrypt contact data
    foreach ($data as &$row) {

        $row["name"] = decryptData($row["name"]);
        $row["email"] = decryptData($row["email"]);
        $row["phone"] = decryptData($row["phone"]);
        $row["service"] = decryptData($row["service"]);
        $row["serviceName"] = decryptData($row["serviceName"]);
        $row["subject"] = decryptData($row["subject"]);
        $row["message"] = decryptData($row["message"]);
        $row["createdOn"] = $row["createdOn"];
        $row["createdBy"] = getUserNamefromDB($row["createdBy"]);
    }

    unset($row);


    // Search after decrypting
    if ($search !== null && $search !== "") {

        $search = strtolower($search);

        $data = array_filter($data, function ($row) use ($search) {

            $fields = [
                $row["id"],
                $row["name"],
                $row["serviceName"],
                $row["email"],
                $row["phone"],
                $row["subject"],
                $row["message"],
                $row["adminMailStatus"],
                $row["customerMailStatus"],
                $row["createdOn"],
                $row["createdBy"]
            ];

            foreach ($fields as $field) {

                if (stripos((string)$field, $search) !== false) {
                    return true;
                }

            }

            return false;
        });

        // Reset array indexes
        $data = array_values($data);
    }


    // If search was provided but nothing matched
    if ($search !== null && count($data) === 0) {
        return [];
    }

    return $data;
}