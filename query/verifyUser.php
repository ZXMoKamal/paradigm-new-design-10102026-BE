<?php

include ("../configs/header.php");

include("../controllers/auth/auth.php");

$user = verifyFirebaseToken();

echo json_encode([
    "message" => "Authenticated successfully",
    "uid" => $user["uid"]
]);