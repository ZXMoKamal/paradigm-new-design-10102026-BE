<?php

require_once("../../configs/connectDB.php");
include("../../configs/header.php");
require_once("../../controllers/auth/auth.php");
require_once(__DIR__."/../../controllers/db/ACL.php");

// Verify Firebase token
$user = verifyFirebaseToken();





    // if($isAdmin){
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
            $user["uid"]
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
        $ACLresult= checkAdminACL($admin["id"],"dashboard","view");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have view access for dashboard"
            ]);

            exit;

        }
    // }



// Check admin
if (!$user["admin"]) {

    http_response_code(403);

    echo json_encode([
        "error" => "Admin access required"
    ]);

    exit;
}



// ==================================================
// Check request method
// ==================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Method not allowed"
    ]);

    exit;
}


// ==================================================
// Get JSON body
// ==================================================

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Invalid JSON body"
    ]);

    exit;
}


// ==================================================
// Get duration
// ==================================================

$duration = $input["duration"] ?? null;

if (!$duration) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Duration is required"
    ]);

    exit;
}


// ==================================================
// Validate duration
// ==================================================

$allowedDurations = [
    "TODAY",
    "LAST_7_DAYS",
    "LAST_30_DAYS",
    "THIS_YEAR"
];

if (!in_array($duration, $allowedDurations, true)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => "Invalid duration",
        "allowedDurations" => $allowedDurations
    ]);

    exit;
}

// ==================================================
// Build date conditions
// ==================================================
//
// Important:
// Each table has a "date" column.
// We use the correct table alias to avoid:
//
// Column 'date' in where clause is ambiguous
//
// ev = events_visits
// sv = services_visits
// bv = blog_visits
// ==================================================

switch ($duration) {

    case "TODAY":

        $eventDateCondition =
            "ev.date = CURDATE()";

        $serviceDateCondition =
            "sv.date = CURDATE()";

        $blogDateCondition =
            "bv.date = CURDATE()";

        break;


    case "LAST_7_DAYS":

        $eventDateCondition =
            "ev.date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";

        $serviceDateCondition =
            "sv.date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";

        $blogDateCondition =
            "bv.date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";

        break;


    case "LAST_30_DAYS":

        $eventDateCondition =
            "ev.date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";

        $serviceDateCondition =
            "sv.date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";

        $blogDateCondition =
            "bv.date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";

        break;


    case "THIS_YEAR":

        $eventDateCondition =
            "YEAR(ev.date) = YEAR(CURDATE())";

        $serviceDateCondition =
            "YEAR(sv.date) = YEAR(CURDATE())";

        $blogDateCondition =
            "YEAR(bv.date) = YEAR(CURDATE())";

        break;
}


// ==================================================
// Events statistics
// ==================================================

$events = [];

$sql = "
    SELECT
        e.id,
        e.name,
        COUNT(ev.id) AS visits

    FROM events_visits ev

    INNER JOIN events e
        ON e.id = ev.eventId

    WHERE $eventDateCondition

    GROUP BY
        e.id,
        e.name

    ORDER BY
        visits DESC
";


$result = $conn->query($sql);


if ($result === false) {

    error_log("Events statistics query failed: " . $conn->error);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Failed to get events statistics"
    ]);

    exit;
}


while ($row = $result->fetch_assoc()) {

    $events[] = [
        "id" => (int) $row["id"],
        "name" => $row["name"],
        "visits" => (int) $row["visits"]
    ];
}


// ==================================================
// Services statistics
// ==================================================

$services = [];

$sql = "
    SELECT
        s.id,
        s.title,
        COUNT(sv.id) AS visits

    FROM services_visits sv

    INNER JOIN services s
        ON s.id = sv.serviceId

    WHERE $serviceDateCondition

    GROUP BY
        s.id,
        s.title

    ORDER BY
        visits DESC
";


$result = $conn->query($sql);


if ($result === false) {

    error_log("Services statistics query failed: " . $conn->error);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Failed to get services statistics"
    ]);

    exit;
}


while ($row = $result->fetch_assoc()) {

    $services[] = [
        "id" => (int) $row["id"],
        "name" => $row["title"],
        "visits" => (int) $row["visits"]
    ];
}


// ==================================================
// Blog / Article statistics
// ==================================================

$blogs = [];

$sql = "
    SELECT
        b.id,
        b.name,
        COUNT(bv.id) AS visits

    FROM blog_visits bv

    INNER JOIN blogs b
        ON b.id = bv.blogId

    WHERE $blogDateCondition

    GROUP BY
        b.id,
        b.name

    ORDER BY
        visits DESC
";


$result = $conn->query($sql);


if ($result === false) {

    error_log("Blog statistics query failed: " . $conn->error);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Failed to get blog statistics"
    ]);

    exit;
}


while ($row = $result->fetch_assoc()) {

    $blogs[] = [
        "id" => (int) $row["id"],
        "name" => $row["name"],
        "visits" => (int) $row["visits"]
    ];
}


// ==================================================
// Return statistics
// ==================================================

http_response_code(200);

echo json_encode([
    "success" => true,
    "duration" => $duration,

    "events" => $events,

    "services" => $services,

    "blogs" => $blogs
]);