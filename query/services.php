<?php
include ("../controllers/db/services.php");
include ("../configs/header.php");


if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $id = array_key_exists("id", $_GET)?htmlspecialchars($_GET['id']):"all";

    if (empty($id) || $id=="all") {
        
        //$get all services data from db

        $services =  getAllServices();


    } else if(ctype_digit($id)) {

        //$if string contain only digits
        //$get service data from db for id 

        $services =  getService($id);
            
    }else{
        
        $services = array("error"=>"Invalid service id");
    }
        
    if(array_key_exists("error",$services)){
        http_response_code(404);
    }else{
        http_response_code(200);
    }

    echo json_encode($services);

}