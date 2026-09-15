<?php
include(__DIR__."/../../configs/connectDB.php");
    require_once(__DIR__."/getAdmins.php");

    
// function getAllBlogs($from, $to) {

//     global $conn;

//     // Valnameate inputs
//     $from = (int)$from;
//     $to = (int)$to;

//     $limit = $to - $from;

//     if ($limit <= 0) {
//         // http_response_code(400);
//         return ["error" => "Invalid range"];
//     }

//     $sql = $sql = "SELECT id, name, requestTitle FROM blogs ORDER BY id DESC LIMIT ?, ?";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ii", $from, $limit);
//     $stmt->execute();

//     $result = $stmt->get_result();

//     $data = [];

//     if (!$result || $result->num_rows == 0) {
//         // http_response_code(404);
//         return ["error" => "No data found"];
//     }

//     // http_response_code(200);
//     $data = $result->fetch_all(MYSQLI_ASSOC);

//     return $data;
// }


// function getBlog($requestTitle) {

//     global $conn;

//     $sql = "SELECT id, name, content, requestTitle FROM blogs WHERE requestTitle = ? LIMIT 1";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $requestTitle);
//     $stmt->execute();

//     $result = $stmt->get_result();

//     if (!$result || $result->num_rows == 0) {
//         return ["error" => "No data found"];
//     }

//     // Fetch single row
//     $data = $result->fetch_assoc();

//     return $data;
// }

function getAllBlogs($from, $to, $isAdmin = false)
{
    global $conn;

    // Validate inputs
    $from = (int)$from;
    $to = (int)$to;

    $limit = $to - $from;

    if ($limit <= 0) {
        return ["error" => "Invalid range"];
    }


    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "SELECT 
                    id,
                    name,
                    requestTitle
                FROM blogs
                ORDER BY id DESC
                LIMIT ?, ?";

    }


    /*
     * Admin users
     */
    else {

        $sql = "SELECT 
                    id,
                    name,
                    requestTitle,
                    createdOn,
                    createdBy,
                    lastUpdateOn,
                    lastUpdateBy
                FROM blogs
                ORDER BY id DESC
                LIMIT ?, ?";
    }


    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $stmt->bind_param("ii", $from, $limit);

    if (!$stmt->execute()) {
        $stmt->close();
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        $stmt->close();
        return ["error" => "No data found"];
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);


    /*
     * Only admins can see creator information.
     */
    if ($isAdmin) {

        foreach ($data as &$blog) {

            if ($blog["createdBy"] !== null) {

                $blog["createdBy"] = getUserNamefromDB(
                    (int)$blog["createdBy"]
                );
            }
            if ($blog["lastUpdateBy"] !== null) {

                $blog["lastUpdateBy"] = getUserNamefromDB(
                    (int)$blog["lastUpdateBy"]
                );
            }
        }

        unset($blog);
    }


    $stmt->close();

    return $data;
}


function getBlog($requestTitle, $isAdmin = false, $visitorId = null)
{
    global $conn;


    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "SELECT 
                    id,
                    name,
                    content,
                    requestTitle
                FROM blogs
                WHERE requestTitle = ?
                LIMIT 1";

    }


    /*
     * Admin users
     */
    else {

        $sql = "SELECT 
                    id,
                    name,
                    content,
                    requestTitle,
                    createdOn,
                    createdBy
                FROM blogs
                WHERE requestTitle = ?
                LIMIT 1";
    }


    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $stmt->bind_param("s", $requestTitle);

    if (!$stmt->execute()) {
        $stmt->close();
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        $stmt->close();
        return ["error" => "No data found"];
    }


    // Fetch single row
    $data = $result->fetch_assoc();


    /*
     * Only admins can see creator information.
     */
    if ($isAdmin) {

        if ($data["createdBy"] !== null) {

            $data["createdBy"] = getUserNamefromDB(
                (int)$data["createdBy"]
            );
        }
    }


    $stmt->close();




    if ($visitorId !== null && trim($visitorId) !== "") {
            $date = date("Y-m-d");

        $sql = "
            INSERT IGNORE INTO blog_visits
            (visitorId, blogId, date)
            VALUES (?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare query"
            ];
        }

        $stmt->bind_param(
            "sis",
            $visitorId,
            $data["id"],
            $date
        );

        if (!$stmt->execute()) {
            error_log($stmt->error);

            $stmt->close();

            return [
                "error" => "Failed to record article visit"
            ];
        }

        $isNewVisit = $stmt->affected_rows === 1;

        $stmt->close();
    }





    return $data;
}

function getBlogById($id, $isAdmin = false, $visitorId = null)
{
    global $conn;


    /*
     * Public users
     */
    if (!$isAdmin) {

        $sql = "SELECT 
                    id,
                    name,
                    content,
                    requestTitle
                FROM blogs
                WHERE id = ?
                LIMIT 1";

    }


    /*
     * Admin users
     */
    else {

        $sql = "SELECT 
                    id,
                    name,
                    content,
                    requestTitle,
                    createdOn,
                    createdBy
                FROM blogs
                WHERE id = ?
                LIMIT 1";
    }


    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare query"];
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        $stmt->close();
        return ["error" => "Failed to execute query"];
    }

    $result = $stmt->get_result();

    if (!$result || $result->num_rows == 0) {
        $stmt->close();
        return ["error" => "No data found"];
    }


    // Fetch single row
    $data = $result->fetch_assoc();


    /*
     * Only admins can see creator information.
     */
    if ($isAdmin) {

        if ($data["createdBy"] !== null) {

            $data["createdBy"] = getUserNamefromDB(
                (int)$data["createdBy"]
            );
        }
    }


    $stmt->close();




    if ($visitorId !== null && trim($visitorId) !== "") {
            $date = date("Y-m-d");

        $sql = "
            INSERT IGNORE INTO blog_visits
            (visitorId, blogId, date)
            VALUES (?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare query"
            ];
        }

        $stmt->bind_param(
            "sis",
            $visitorId,
            $data["id"],
            $date
        );

        if (!$stmt->execute()) {
            error_log($stmt->error);

            $stmt->close();

            return [
                "error" => "Failed to record article visit"
            ];
        }

        $isNewVisit = $stmt->affected_rows === 1;

        $stmt->close();
    }





    return $data;
}


function addBlog($name, $content, $requestTitle, $uid)
{
    global $conn;

    /*
     * Get admin database ID using Firebase UID
     */
    $adminSql = "SELECT id FROM admins WHERE fbuid = ? LIMIT 1";

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

    $createdBy = $admin["id"];
    $lastUpdateBy = $admin["id"];

    $adminStmt->close();


    /*
     * Create date/time
     */
    $createdOn = date("Y-m-d");//H:i:s
    $lastUpdateOn = date("Y-m-d");//H:i:s

    /*
     * Insert blog
     */
    $sql = "
        INSERT INTO blogs (
            name,
            content,
            requestTitle,
            createdBy,
            createdOn,
            lastUpdateBy,
            lastUpdateOn
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);

        return [
            "error" => "Failed to prepare database query"
        ];
    }

    $stmt->bind_param(
        "sssisis",
        $name,
        $content,
        $requestTitle,
        $createdBy,
        $createdOn,
        $lastUpdateBy,
        $lastUpdateOn
    );

    if (!$stmt->execute()) {
        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to create blog"
        ];
    }

    $blogId = $conn->insert_id;

    $stmt->close();

    return [
        "id" => $blogId,
        "createdBy" => $createdBy,
        "createdOn" => $createdOn
    ];
}


// function addBlog($name, $content, $requestTitle,$uid)
// {
//     global $conn;

//     $sql = "INSERT INTO blogs (name, content, requestTitle) VALUES (?, ?, ?)";

//     $stmt = $conn->prepare($sql);

//     if (!$stmt) {
//         error_log($conn->error);
//         return [
//             "error" => "Failed to prepare database query"
//         ];
//     }

//     $stmt->bind_param(
//         "sss",
//         $name,
//         $content,
//         $requestTitle
//     );

//     if (!$stmt->execute()) {
//         error_log($stmt->error);

//         $stmt->close();

//         return [
//             "error" => "Failed to create event"
//         ];
//     }

//     $blogId = $conn->insert_id;

//     $stmt->close();

//     return [
//         "id" => $blogId
//     ];
// }

function updateBlog(
    $blogId,
    $name,
    $content,
    // $requestTitle,
    $uid
) {
    global $conn;


    // ---------------------------------------------------------
    // Get admin database ID using Firebase UID
    // ---------------------------------------------------------

    $adminSql = "
        SELECT id
        FROM admins
        WHERE fbuid = ?
        LIMIT 1
    ";


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

    $lastUpdateBy = $admin["id"];


    $adminStmt->close();


    // ---------------------------------------------------------
    // Last update date
    // ---------------------------------------------------------

    $lastUpdateOn = date("Y-m-d");


    // ---------------------------------------------------------
    // Update blog
    // ---------------------------------------------------------

    $sql = "
        UPDATE blogs
        SET
            name = ?,
            content = ?,
            lastUpdateOn = ?,
            lastUpdateBy = ?
        WHERE id = ?
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        error_log($conn->error);

        return [
            "error" => "Failed to prepare database query"
        ];
    }


    $stmt->bind_param(
        "sssii",
        $name,
        $content,
        // $requestTitle,
        $lastUpdateOn,
        $lastUpdateBy,
        $blogId
    );


    if (!$stmt->execute()) {

        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to update blog"
        ];
    }


    $stmt->close();


    return [
        "id" => $blogId,
        "lastUpdateBy" => $lastUpdateBy,
        "lastUpdateOn" => $lastUpdateOn
    ];
}


function deleteBlog($id)
{
    global $conn;

    $id = (int)$id;

    if ($id <= 0) {
        return [
            "error" => "Invalid blog ID"
        ];
    }

    // Check if blog exists
    $sql = "SELECT id FROM blogs WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);

        return [
            "error" => "Failed to prepare database query"
        ];
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {

        $stmt->close();

        return [
            "error" => "Blog not found"
        ];
    }

    $stmt->close();


    // Delete blog
    $sql = "DELETE FROM blogs WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log($conn->error);

        return [
            "error" => "Failed to prepare delete query"
        ];
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {

        error_log($stmt->error);

        $stmt->close();

        return [
            "error" => "Failed to delete blog"
        ];
    }

    $stmt->close();

    return [
        "success" => true,
        "id" => $id
    ];
}