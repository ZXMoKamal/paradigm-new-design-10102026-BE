<?php
include ("../controllers/db/services.php");
include ("../configs/header.php");


if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $requestTitle = array_key_exists("requestTitle", $_GET)?htmlspecialchars($_GET['requestTitle']):"all";

    if (empty($requestTitle) || $requestTitle=="all") {
        
        //$get all services data from db

        $services =  getAllServices();


    } else if($requestTitle) {

        //$if string contain only digits
        //$get service data from db for id 
        $requestTitle = html_entity_decode(urldecode($requestTitle), ENT_QUOTES | ENT_HTML5);
        $services =  getService($requestTitle);

    }
        
    if(array_key_exists("error",$services)){
        http_response_code(404);
    }else{
        http_response_code(200);
    }

    echo json_encode($services);

}