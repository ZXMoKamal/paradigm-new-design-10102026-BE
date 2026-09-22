<?php
    include(__DIR__."/../../configs/connectDB.php");
    include(__DIR__."/../../enc/enc_dec.php");
    // include_once(__DIR__."/services.php");
    require_once(__DIR__."/getAdmins.php");
    require_once(__DIR__."/ACL.php");

    function setContactUs( $name, $email, $phone, $subject, $service, $serviceName, $message, $adminMailStatus, $customerMailStatus) {
        global $conn;

        $nameEncrypted = encryptData($name);
        $emailEncrypted = encryptData($email);
        $phoneEncrypted = encryptData($phone);
        $subjectEncrypted = encryptData($subject);
        $messageEncrypted = encryptData($message);
        $serviceEncrypted = encryptData($service);
        $serviceNameEncrypted = encryptData($serviceName);

    $createdOn = date("Y-m-d");
    $createdBy = 88; // automatic

        // ✅ Insert into DB
        $sql = "INSERT INTO contactUs (name, email, phone, subject, service, serviceName, message, adminMailStatus, customerMailStatus, createdOn, createdBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssiisi", $nameEncrypted, $emailEncrypted, $phoneEncrypted, $subjectEncrypted, $serviceEncrypted, $serviceNameEncrypted, $messageEncrypted, $adminMailStatus, $customerMailStatus, $createdOn, $createdBy);

        if ($stmt->execute()) {
            $state = true;
        } else {
            $state = false;
        }

        $stmt->close();
        $conn->close();

        return $state;
    }



// function getAllContactUs($from, $to, $search = null,$uid)
// {
//     global $conn;

    
//     // if($isAdmin){
//         /*
//         * Get admin database ID using Firebase UID
//         */
//         $adminSql = "SELECT id FROM admins WHERE fbuid = ? LIMIT 1";

//         $adminStmt = $conn->prepare($adminSql);

//         if (!$adminStmt) {
//             error_log($conn->error);

//             return [
//                 "error" => "Failed to prepare admin query"
//             ];
//         }

//         $adminStmt->bind_param(
//             "s",
//             $uid
//         );

//         if (!$adminStmt->execute()) {
//             error_log($adminStmt->error);

//             $adminStmt->close();

//             return [
//                 "error" => "Failed to find admin"
//             ];
//         }

//         $adminResult = $adminStmt->get_result();

//         if ($adminResult->num_rows === 0) {
//             $adminStmt->close();

//             return [
//                 "error" => "Admin not found"
//             ];
//         }

//         $admin = $adminResult->fetch_assoc();
//         $ACLresult= checkAdminACL($admin["id"],"contacts","view");
//         if(!($ACLresult["success"] && $ACLresult["allowed"])){

//             http_response_code(403);

//             echo json_encode([
//                 "error" => "You do not have view access for contacts"
//             ]);

//             exit;

//         }
//     // }

//     // Validate pagination values
//     $from = max(0, (int)$from);
//     $to   = max($from, (int)$to);

//     $limit = $to - $from;

//     if ($limit <= 0) {
//         return [
//             "error" => "Invalid pagination range"
//         ];
//     }

//     $sql = "SELECT 
//                 id,
//                 name,
//                 email,
//                 phone,
//                 subject,
//                 service,
//                 serviceName,
//                 message,
//                 adminMailStatus,
//                 customerMailStatus,
//                 createdOn,
//                 createdBy
//             FROM contactUs
//             ORDER BY id DESC
//             LIMIT ?, ?";

//     $stmt = $conn->prepare($sql);

//     if (!$stmt) {
//         return [
//             "error" => "Failed to prepare query"
//         ];
//     }

//     $stmt->bind_param("ii", $from, $limit);

//     if (!$stmt->execute()) {
//         return [
//             "error" => "Failed to execute query"
//         ];
//     }

//     $result = $stmt->get_result();

//     if (!$result || $result->num_rows == 0) {
//         return [
//             "error" => "No contact messages found"
//         ];
//     }

//     $data = $result->fetch_all(MYSQLI_ASSOC);

//     $stmt->close();

//     // Decode/decrypt contact data
//     foreach ($data as &$row) {

//         $row["name"] = decryptData($row["name"]);
//         $row["email"] = decryptData($row["email"]);
//         $row["phone"] = decryptData($row["phone"]);
//         $row["service"] = decryptData($row["service"]);
//         $row["serviceName"] = decryptData($row["serviceName"]);
//         $row["subject"] = decryptData($row["subject"]);
//         $row["message"] = decryptData($row["message"]);
//         $row["createdOn"] = $row["createdOn"];
//         $row["createdBy"] = getUserNamefromDB($row["createdBy"],$uid);
//     }

//     unset($row);


//     // Search after decrypting
//     if ($search !== null && $search !== "") {

//         $search = strtolower($search);

//         $data = array_filter($data, function ($row) use ($search) {

//             $fields = [
//                 $row["id"],
//                 $row["name"],
//                 $row["serviceName"],
//                 $row["email"],
//                 $row["phone"],
//                 $row["subject"],
//                 $row["message"],
//                 $row["adminMailStatus"],
//                 $row["customerMailStatus"],
//                 $row["createdOn"],
//                 $row["createdBy"]
//             ];

//             foreach ($fields as $field) {

//                 if (stripos((string)$field, $search) !== false) {
//                     return true;
//                 }

//             }

//             return false;
//         });

//         // Reset array indexes
//         $data = array_values($data);
//     }


//     // If search was provided but nothing matched
//     if ($search !== null && count($data) === 0) {
//         return [];
//     }

//     return $data;
// }


// function getAllContactUs(
//     $from,
//     $to,
//     $search = null,
//     $uid,
//     $filterBy = "",
//     $groupBy = ""
// ) {
//     global $conn;


//     // =========================================================
//     // Get admin database ID using Firebase UID
//     // =========================================================

//     $adminSql = "SELECT id
//                  FROM admins
//                  WHERE fbuid = ?
//                  LIMIT 1";

//     $adminStmt = $conn->prepare($adminSql);

//     if (!$adminStmt) {

//         error_log($conn->error);

//         return [
//             "error" => "Failed to prepare admin query"
//         ];
//     }

//     $adminStmt->bind_param(
//         "s",
//         $uid
//     );

//     if (!$adminStmt->execute()) {

//         error_log($adminStmt->error);

//         $adminStmt->close();

//         return [
//             "error" => "Failed to find admin"
//         ];
//     }

//     $adminResult = $adminStmt->get_result();

//     if ($adminResult->num_rows === 0) {

//         $adminStmt->close();

//         return [
//             "error" => "Admin not found"
//         ];
//     }

//     $admin = $adminResult->fetch_assoc();

//     $adminStmt->close();


//     // =========================================================
//     // Check Contacts View ACL
//     // =========================================================

//     $ACLresult = checkAdminACL(
//         $admin["id"],
//         "contacts",
//         "view"
//     );

//     if (
//         !(
//             $ACLresult["success"] &&
//             $ACLresult["allowed"]
//         )
//     ) {

//         http_response_code(403);

//         echo json_encode([
//             "error" => "You do not have view access for contacts"
//         ]);

//         exit;
//     }


//     // =========================================================
//     // Validate pagination
//     // =========================================================

//     $from = max(0, (int)$from);
//     $to   = max($from, (int)$to);

//     $limit = $to - $from;

//     if ($limit <= 0) {

//         return [
//             "error" => "Invalid pagination range"
//         ];
//     }


//     // =========================================================
//     // Get all contacts
//     //
//     // IMPORTANT:
//     // Search is done AFTER decrypting because the database
//     // contains encrypted values.
//     // =========================================================

//     $sql = "SELECT
//                 id,
//                 name,
//                 email,
//                 phone,
//                 subject,
//                 service,
//                 serviceName,
//                 message,
//                 adminMailStatus,
//                 customerMailStatus,
//                 createdOn,
//                 createdBy
//             FROM contactUs
//             ORDER BY id DESC
//             LIMIT ?, ?";

//     $stmt = $conn->prepare($sql);

//     if (!$stmt) {

//         error_log($conn->error);

//         return [
//             "error" => "Failed to prepare query"
//         ];
//     }

//     $stmt->bind_param(
//         "ii",
//         $from,
//         $limit
//     );

//     if (!$stmt->execute()) {

//         error_log($stmt->error);

//         $stmt->close();

//         return [
//             "error" => "Failed to execute query"
//         ];
//     }

//     $result = $stmt->get_result();

//     if (!$result || $result->num_rows === 0) {

//         $stmt->close();

//         return [
//             "error" => "No contact messages found"
//         ];
//     }

//     $data = $result->fetch_all(MYSQLI_ASSOC);

//     $stmt->close();


//     // =========================================================
//     // Decrypt contact data
//     // =========================================================

//     foreach ($data as &$row) {

//         $row["name"] = decryptData($row["name"]);

//         $row["email"] = decryptData($row["email"]);

//         $row["phone"] = decryptData($row["phone"]);

//         $row["service"] = decryptData($row["service"]);

//         $row["serviceName"] = decryptData($row["serviceName"]);

//         $row["subject"] = decryptData($row["subject"]);

//         $row["message"] = decryptData($row["message"]);

//         $row["createdBy"] = getUserNamefromDB(
//             $row["createdBy"],
//             $uid
//         );
//     }

//     unset($row);


//     // =========================================================
//     // Allowed FILTER fields
//     //
//     // These are the decrypted/frontend field names.
//     // =========================================================

//     $allowedFilterFields = [

//         "id" => "id",

//         "name" => "name",

//         "email" => "email",

//         "phone" => "phone",

//         "subject" => "subject",

//         "service" => "serviceName",

//         "message" => "message",

//         "createdBy" => "createdBy",

//         "createdOn" => "createdOn",
//     ];


//     // =========================================================
//     // Allowed GROUP fields
//     // =========================================================

//     $allowedGroupFields = [

//         "name" => "name",

//         "email" => "email",

//         "service" => "serviceName",

//         "createdBy" => "createdBy",

//         "createdOn" => "createdOn",

//         "adminMailStatus" => "adminMailStatus",

//         "customerMailStatus" => "customerMailStatus",
//     ];


//     // =========================================================
//     // SEARCH
//     //
//     // If filterBy exists:
//     //     search only that field.
//     //
//     // If filterBy is empty:
//     //     search all supported fields.
//     // =========================================================

//     if ($search !== "") {

//         $search = trim($search);

//         // -----------------------------------------------------
//         // Search specific field
//         // -----------------------------------------------------

//         if (
//             $filterBy !== "" &&
//             isset($allowedFilterFields[$filterBy])
//         ) {

//             $field = $allowedFilterFields[$filterBy];

//             $data = array_filter(
//                 $data,
//                 function ($row) use ($field, $search) {

//                     $value = $row[$field] ?? "";

//                     return stripos(
//                         (string)$value,
//                         $search
//                     ) !== false;
//                 }
//             );

//         }

//         // -----------------------------------------------------
//         // Search all fields
//         // -----------------------------------------------------

//         else {

//             $data = array_filter(
//                 $data,
//                 function ($row) use ($search) {

//                     $fields = [

//                         $row["id"] ?? "",

//                         $row["name"] ?? "",

//                         $row["email"] ?? "",

//                         $row["phone"] ?? "",

//                         $row["subject"] ?? "",

//                         $row["serviceName"] ?? "",

//                         $row["message"] ?? "",

//                         $row["adminMailStatus"] ?? "",

//                         $row["customerMailStatus"] ?? "",

//                         $row["createdOn"] ?? "",

//                         $row["createdBy"] ?? "",
//                     ];


//                     foreach ($fields as $field) {

//                         if (
//                             stripos(
//                                 (string)$field,
//                                 $search
//                             ) !== false
//                         ) {

//                             return true;
//                         }
//                     }


//                     return false;
//                 }
//             );
//         }


//         // Reset indexes
//         $data = array_values($data);
//     }


//     // =========================================================
//     // GROUP / SORT
//     //
//     // IMPORTANT:
//     //
//     // We DO NOT use SQL GROUP BY.
//     //
//     // GROUP BY would merge rows together.
//     //
//     // Instead, we sort the complete result by the selected
//     // group field. The React frontend can then create
//     // collapsible groups.
//     // =========================================================

//     if (
//         $groupBy !== "" &&
//         isset($allowedGroupFields[$groupBy])
//     ) {

//         $groupField = $allowedGroupFields[$groupBy];

//         usort(
//             $data,
//             function ($a, $b) use ($groupField) {

//                 $valueA = $a[$groupField] ?? "";

//                 $valueB = $b[$groupField] ?? "";


//                 // Convert values to strings
//                 $valueA = (string)$valueA;
//                 $valueB = (string)$valueB;


//                 // Case-insensitive sorting
//                 $comparison = strcasecmp(
//                     $valueA,
//                     $valueB
//                 );


//                 // If same group, newest ID first
//                 if ($comparison === 0) {

//                     return (int)$b["id"] <=> (int)$a["id"];
//                 }


//                 return $comparison;
//             }
//         );
//     }


//     // =========================================================
//     // No search + no grouping
//     //
//     // Keep newest contacts first.
//     // =========================================================

//     else if ($groupBy === "") {

//         usort(
//             $data,
//             function ($a, $b) {

//                 return (int)$b["id"] <=> (int)$a["id"];
//             }
//         );
//     }


//     // =========================================================
//     // Search returned nothing
//     // =========================================================

//     if (
//         $search !== "" &&
//         count($data) === 0
//     ) {

//         return [];
//     }


//     // =========================================================
//     // Return contacts
//     // =========================================================

//     return array_values($data);
// }



function getAllContactUs(
    $from,
    $to,
    $search = null,
    $uid,
    $filterBy = "",
    $groupBy = ""
) {

    global $conn;


    // =========================================================
    // Get admin database ID using Firebase UID
    // =========================================================

    $adminSql = "SELECT id
                 FROM admins
                 WHERE fbuid = ?
                 LIMIT 1";

    $adminStmt = $conn->prepare($adminSql);

    if (!$adminStmt) {

        error_log($conn->error);

        return [
            "error" => "Failed to prepare admin query"
        ];
    }

    $adminStmt->bind_param(
        "s",
        $uid
    );

    if (!$adminStmt->execute()) {

        error_log($adminStmt->error);

        $adminStmt->close();

        return [
            "error" => "Failed to find admin"
        ];
    }

    $adminResult = $adminStmt->get_result();

    if ($adminResult->num_rows === 0) {

        $adminStmt->close();

        return [
            "error" => "Admin not found"
        ];
    }

    $admin = $adminResult->fetch_assoc();

    $adminStmt->close();


    // =========================================================
    // Check Contacts View ACL
    // =========================================================

    $ACLresult = checkAdminACL(
        $admin["id"],
        "contacts",
        "view"
    );

    if (
        !(
            $ACLresult["success"] &&
            $ACLresult["allowed"]
        )
    ) {

        http_response_code(403);

        echo json_encode([
            "error" => "You do not have view access for contacts"
        ]);

        exit;
    }


    // =========================================================
    // Validate pagination
    // =========================================================

    $from = max(0, (int)$from);

    $to = max($from, (int)$to);

    $limit = $to - $from;

    if ($limit <= 0) {

        return [
            "error" => "Invalid pagination range"
        ];
    }


    // =========================================================
    // Get all contacts
    //
    // IMPORTANT:
    // We intentionally do NOT use SQL LIMIT here.
    //
    // Search/filter/group operate after decrypting the data.
    // Therefore pagination must happen after those operations.
    // =========================================================

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
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        error_log($conn->error);

        return [
            "error" => "Failed to prepare query"
        ];
    }

    if (!$stmt->execute()) {

        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to execute query"
        ];
    }

    $result = $stmt->get_result();

    if (!$result) {

        $stmt->close();

        return [
            "error" => "Failed to get contacts"
        ];
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();


    // =========================================================
    // Decrypt contact data
    // =========================================================

    foreach ($data as &$row) {

        $row["name"] = decryptData($row["name"]);

        $row["email"] = decryptData($row["email"]);

        $row["phone"] = decryptData($row["phone"]);

        $row["service"] = decryptData($row["service"]);

        $row["serviceName"] = decryptData($row["serviceName"]);

        $row["subject"] = decryptData($row["subject"]);

        $row["message"] = decryptData($row["message"]);

        $row["createdBy"] = getUserNamefromDB(
            $row["createdBy"],
            $uid
        );
    }

    unset($row);


    // =========================================================
    // Allowed FILTER fields
    // =========================================================

    $allowedFilterFields = [

        "id" => "id",

        "name" => "name",

        "email" => "email",

        "phone" => "phone",

        "subject" => "subject",

        "service" => "serviceName",

        "message" => "message",

        "createdBy" => "createdBy",

        "createdOn" => "createdOn",
    ];


    // =========================================================
    // Allowed GROUP fields
    // =========================================================

    $allowedGroupFields = [

        "name" => "name",

        "email" => "email",

        "service" => "serviceName",

        "createdBy" => "createdBy",

        "createdOn" => "createdOn",

        "adminMailStatus" => "adminMailStatus",

        "customerMailStatus" => "customerMailStatus",
    ];


    // =========================================================
    // SEARCH
    // =========================================================

    if ($search !== "") {

        $search = trim($search);


        // -----------------------------------------------------
        // Search specific field
        // -----------------------------------------------------

        if (
            $filterBy !== "" &&
            isset($allowedFilterFields[$filterBy])
        ) {

            $field = $allowedFilterFields[$filterBy];

            $data = array_filter(
                $data,
                function ($row) use ($field, $search) {

                    $value = $row[$field] ?? "";

                    return stripos(
                        (string)$value,
                        $search
                    ) !== false;
                }
            );

        }


        // -----------------------------------------------------
        // Search all fields
        // -----------------------------------------------------

        else {

            $data = array_filter(
                $data,
                function ($row) use ($search) {

                    $fields = [

                        $row["id"] ?? "",

                        $row["name"] ?? "",

                        $row["email"] ?? "",

                        $row["phone"] ?? "",

                        $row["subject"] ?? "",

                        $row["serviceName"] ?? "",

                        $row["message"] ?? "",

                        $row["adminMailStatus"] ?? "",

                        $row["customerMailStatus"] ?? "",

                        $row["createdOn"] ?? "",

                        $row["createdBy"] ?? "",
                    ];


                    foreach ($fields as $field) {

                        if (
                            stripos(
                                (string)$field,
                                $search
                            ) !== false
                        ) {

                            return true;
                        }
                    }


                    return false;
                }
            );
        }


        // Reset indexes

        $data = array_values($data);
    }


    // =========================================================
    // GROUP / SORT
    // =========================================================

    if (
        $groupBy !== "" &&
        isset($allowedGroupFields[$groupBy])
    ) {

        $groupField = $allowedGroupFields[$groupBy];

        usort(
            $data,
            function ($a, $b) use ($groupField) {

                $valueA = $a[$groupField] ?? "";

                $valueB = $b[$groupField] ?? "";


                // Convert values to strings

                $valueA = (string)$valueA;

                $valueB = (string)$valueB;


                // Case-insensitive sorting

                $comparison = strcasecmp(
                    $valueA,
                    $valueB
                );


                // If same group, newest ID first

                if ($comparison === 0) {

                    return (int)$b["id"] <=> (int)$a["id"];
                }


                return $comparison;
            }
        );
    }


    // =========================================================
    // No grouping
    //
    // Keep newest contacts first.
    // =========================================================

    else if ($groupBy === "") {

        usort(
            $data,
            function ($a, $b) {

                return (int)$b["id"] <=> (int)$a["id"];
            }
        );
    }


    // =========================================================
    // TOTAL RESULTS
    //
    // IMPORTANT:
    // This is calculated AFTER search/filter/group preparation.
    // Therefore it represents the actual result set.
    // =========================================================

    $total = count($data);


    // =========================================================
    // Pagination calculations
    // =========================================================

    $page = (int)floor($from / $limit) + 1;

    $totalPages = $total > 0
        ? (int)ceil($total / $limit)
        : 0;


    // =========================================================
    // Search returned nothing
    // =========================================================

    if (
        $search !== "" &&
        $total === 0
    ) {

        return [
            "data" => [],

            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => 0,
                "totalPages" => 0,
                "hasNextPage" => false,
                "hasPreviousPage" => false,
            ]
        ];
    }


    // =========================================================
    // Apply pagination
    //
    // Pagination happens AFTER:
    //
    // 1. Decryption
    // 2. Search
    // 3. Filtering
    // 4. Group sorting
    // =========================================================

    $paginatedData = array_slice(
        $data,
        $from,
        $limit
    );


    // =========================================================
    // Return contacts + pagination information
    // =========================================================

    return [

        "data" => array_values($paginatedData),

        "pagination" => [

            "page" => $page,

            "limit" => $limit,

            "total" => $total,

            "totalPages" => $totalPages,

            "hasNextPage" => $page < $totalPages,

            "hasPreviousPage" => $page > 1,
        ]
    ];
}