<?php
include ("../controllers/db/services.php");
include ("../configs/header.php");
require_once("../controllers/auth/auth.php");


if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $user = verifyFirebaseToken(false);

    $isAdmin = false;
    $userId = null;

    if ($user && $user["admin"] === true) {
        $isAdmin = true;
        // $userId = $user["id"];
    }

    $requestTitle = array_key_exists("requestTitle", $_GET)?htmlspecialchars($_GET['requestTitle']):"all";

    if (empty($requestTitle) || $requestTitle=="all") {
        
        //$get all services data from db

        $services =  getAllServices($isAdmin);


    } else if($requestTitle) {

        //$if string contain only digits
        //$get service data from db for id 
        $requestTitle = html_entity_decode(urldecode($requestTitle), ENT_QUOTES | ENT_HTML5);
        if(isset($_GET["visitorId"]) && !empty($_GET["visitorId"])){
            $visitorId = htmlspecialchars($_GET["visitorId"]);
            $services =  getService($requestTitle,$isAdmin,$visitorId);
        }else{
            $services =  getService($requestTitle,$isAdmin);
        }
    }
        
    if(array_key_exists("error",$services)){
        http_response_code(404);
    }else{
        http_response_code(200);
    }

    echo json_encode($services);

}