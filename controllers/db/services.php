<?php
include("../configs/connectDB.php");

function getAllServices() {
     
    global $conn;

    $sql = "SELECT id, title, description, icon_code, requestTitle FROM services";
    $result = $conn->query($sql);

    $data = [];

    if(!$result||$result->num_rows == null){
        $data = ["error" => "No data found"];
    }
    else if ($result->num_rows > 0) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
    }

    return $data;
}


function getService($requestTitle) {

    global $conn;

    $sql = "
        SELECT 
            s.id, 
            s.title, 
            s.description,
            s.icon_code,
            s.requestTitle,
            CONCAT(
                '[',
                IFNULL(
                    GROUP_CONCAT(
                        CONCAT(
                            '{\"id\":', p.id, 
                            ',\"point\":\"', REPLACE(p.point, '\"', '\\\\\"'), '\"}'
                        )
                    ),
                    ''
                ),
                ']'
            ) AS points
        FROM services s
        LEFT JOIN services_points p ON p.sid = s.id
        WHERE s.requestTitle = ?
        GROUP BY s.id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $requestTitle);
    $stmt->execute();

    $result = $stmt->get_result();

    
    // ❌ No data
    if (!$result || $result->num_rows == 0) {
        return ["error" => "Service not found"];
    }
        
    $data = [];

    // ✅ Fetch
    $data = $result->fetch_assoc();

    // 🔥 Convert JSON string → array
    // foreach ($data as &$row) {
        $data['points'] = json_decode($data['points'], true);
    // }

    return $data;
}