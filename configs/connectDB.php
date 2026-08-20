<?php

$config = require __DIR__ . "/../../../../../configs/paradigm-config.php";


    $host = $config["db"]["host"];      // Database host
    $dbname = $config["db"]["name"];  // Your database name
    $username = $config["db"]["user"];       // Database username
    $password = $config["db"]["password"];           // Database password

    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // // Optional: set charset
    // $conn->set_charset("utf8mb4");
?>