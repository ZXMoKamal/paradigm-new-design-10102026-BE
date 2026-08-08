<?php
include ("../controllers/db/events.php");
include ("../controllers/storage/events.php");
include ("../configs/header.php");


if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $name = array_key_exists("name", $_GET)?htmlspecialchars($_GET['name']):"all";

    if (empty($name) || $name=="all") {
        
        //$get all events data from db

        $events =  getAllEvents(0,20);

        if(array_key_exists("error",$events)){
            http_response_code(404);
            echo json_encode($events);
            die();
        }else{
            http_response_code(200);
            //$get events images

            foreach ($events as &$event) {

                // Get thumb URL using your function
                $thumb = getEventThumb($event['id']);

                // Add it to the same array
                $event['thumb'] = $thumb;
            }

            echo json_encode($events,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

    } else if($name) {

        //$if string contain only digits
        //$get event data from db for name 

        $name = html_entity_decode(urldecode($name), ENT_QUOTES | ENT_HTML5);
        $events =  getEvent($name);

        if(array_key_exists("error",$events)){
            http_response_code(404);
            echo json_encode($events);
            die();
        }else{
            http_response_code(200);
            //$get events images

            $thumb = getEventThumb($events['id']);
            $events['thumb'] = $thumb;

            $images = getEventImages($events['id']);
            $events['images'] = $images;

            echo json_encode($events,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
            
    }

}