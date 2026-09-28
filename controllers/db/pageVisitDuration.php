<?php

include(__DIR__."/../../configs/connectDB.php");

function addServiceDuration(
    $visitorId,
    $serviceId,
    $duration,
    $page
) {
    global $conn;

    $table = null;

    if($page=="service"){
        $table = "services_visits";
    }else if($page=="article"){
        $table = "blog_visits";
    }else if($page=="event"){
        $table = "events_visits";
    }

    $sql = "
        INSERT INTO ".$table."
            (visitorId, serviceId, date, duration)
        VALUES
            (?, ?, CURDATE(), ?)
        ON DUPLICATE KEY UPDATE
            duration = duration + VALUES(duration)
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $visitorId,
        $serviceId,
        $duration
    );

    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $result;
}