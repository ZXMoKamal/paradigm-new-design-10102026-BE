<?php

include("../configs/connectDB.php");
include ("../controllers/storage/events.php");
include ("../controllers/storage/blogs.php");


function searchData($search)
{
    global $conn;

    $search = trim($search);

    if ($search === "") {
        return ["error" => "Search query is required"];
    }

    $results = [];

    $term = "%" . $search . "%";


    /*
    |--------------------------------------------------------------------------
    | Search Events
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT id, name, description, requestTitle
            FROM events
            WHERE name LIKE ?
               OR description LIKE ?
               OR client LIKE ?
               OR location LIKE ?
               OR requestTitle LIKE ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare events query"];
    }

    $stmt->bind_param(
        "sssss",
        $term,
        $term,
        $term,
        $term,
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $results[] = [
            "page" => "events",
            "id" => $row["id"],
            "title" => $row["name"],
            "description" => $row["description"],
            "requestTitle" => $row["requestTitle"],
            "image" => getEventThumb($row["id"])
        ];
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Search Services
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT id, title, description, requestTitle, icon_code
            FROM services
            WHERE title LIKE ?
               OR description LIKE ?
               OR requestTitle LIKE ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare services query"];
    }

    $stmt->bind_param(
        "sss",
        $term,
        $term,
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $results[] = [
            "page" => "services",
            "id" => $row["id"],
            "title" => $row["title"],
            "description" => $row["description"],
            "requestTitle" => $row["requestTitle"],
            "icon_code" => $row["icon_code"]
        ];
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Search Services Points
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT
                p.id,
                p.sid,
                p.point,
                s.title AS service_name,
                s.requestTitle AS service_requestTitle,
                s.icon_code AS service_icon_code
            FROM services_points p
            INNER JOIN services s ON s.id = p.sid
            WHERE p.point LIKE ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare services points query"];
    }

    $stmt->bind_param(
        "s",
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $results[] = [
            "page" => "services",
            "id" => $row["id"],
            "service_id" => $row["sid"],
            "title" => $row["service_name"],
            "description" => $row["point"],
            "requestTitle" => $row["service_requestTitle"],
            "icon_code" => $row["service_icon_code"]
        ];
    }

    $stmt->close();

    
    /*
    |--------------------------------------------------------------------------
    | Search Blogs
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT id, name, content, requestTitle
            FROM blogs
            WHERE name LIKE ?
               OR content LIKE ?
               OR requestTitle LIKE ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["error" => "Failed to prepare blogs query"];
    }

    $stmt->bind_param(
        "sss",
        $term,
        $term,
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $results[] = [
            "page" => "article",
            "id" => $row["id"],
            "title" => $row["name"],
            // "content" => $row["content"],
            "requestTitle" => $row["requestTitle"],
            "image" => getBlogThumb($row["id"])
        ];
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | No results
    |--------------------------------------------------------------------------
    */

    if (count($results) === 0) {
        return ["error" => "No results found"];
    }

    return $results;
}
