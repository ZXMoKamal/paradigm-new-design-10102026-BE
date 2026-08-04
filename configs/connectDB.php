<?php
    $host = "localhost";      // Database host
    $dbname = "paradigm-portfolio";  // Your database name
    $username = "root";       // Database username
    $password = "";           // Database password

    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // // Optional: set charset
    // $conn->set_charset("utf8mb4");

    echo "Connected successfully";
?>