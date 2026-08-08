<?php
include ("../controllers/db/services.php");
include ("../configs/header.php");


if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $title = array_key_exists("title", $_GET)?htmlspecialchars($_GET['title']):"all";

    if (empty($title) || $title=="all") {
        
        //$get all services data from db

        $services =  getAllServices();


    } else if($title) {

        //$if string contain only digits
        //$get service data from db for id 
        $title = html_entity_decode(urldecode($title), ENT_QUOTES | ENT_HTML5);
        $services =  getService($title);

    }
        
    if(array_key_exists("error",$services)){
        http_response_code(404);
    }else{
        http_response_code(200);
    }

    echo json_encode($services);

}