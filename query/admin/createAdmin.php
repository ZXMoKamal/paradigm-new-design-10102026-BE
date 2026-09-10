<?php

include("../../configs/header.php");

require_once("../../controllers/auth/auth.php");
require_once("../../configs/firebase.php");
require_once("../../configs/connectDB.php");

// Verify Firebase token
$user = verifyFirebaseToken();


// Check admin
if (!$user["admin"]) {

    http_response_code(403);

    echo json_encode([
        "error" => "Admin access required"
    ]);

    exit;
}


// Only POST is allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}


// Get input
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$name = trim($_POST["name"] ?? "");


// Validate input
if ($email === "" || $password === "" || $name === "") {

    http_response_code(400);

    echo json_encode([
        "error" => "Email, password and name are required"
    ]);

    exit;
}


// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid email address"
    ]);

    exit;
}


// Validate password
if (strlen($password) < 6) {

    http_response_code(400);

    echo json_encode([
        "error" => "Password must be at least 6 characters"
    ]);

    exit;
}


try {

    // Create Firebase user
    $createdUser = $auth->createUser([
        "email" => $email,
        "password" => $password,
        "displayName" => $name
    ]);


    // Firebase UID
    $uid = $createdUser->uid;

    // set as admin in Firebase
    $auth->setCustomUserClaims($uid, [
        "admin" => true
    ]);

    // Save admin in database
    $sql = "INSERT INTO admins (fbuid, name)
            VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare database query");
    }

    $stmt->bind_param("ss", $uid, $name);

    if (!$stmt->execute()) {
        throw new Exception("Failed to save admin");
    }

    $stmt->close();


    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Admin account created successfully",
        "admin" => [
            "uid" => $uid,
            "email" => $email,
            "name" => $name
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


} catch (\Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        "error" => "Failed to create admin account"
    ]);

}