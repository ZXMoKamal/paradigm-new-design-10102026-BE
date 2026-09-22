<?php
include ("../controllers/db/events.php");
include ("../controllers/storage/events.php");
include ("../configs/header.php");
require_once("../controllers/auth/auth.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $user = verifyFirebaseToken(false);

    $isAdmin = false;
    $userUId = null;

    if ($user && $user["admin"] === true) {
        $isAdmin = true;
        $userUId = $user["uid"];
    }

    $requestTitle = array_key_exists("requestTitle", $_GET)?htmlspecialchars($_GET['requestTitle']):"all";

    if (empty($requestTitle) || $requestTitle=="all") {
        
        //$get all events data from db

        $events =  getAllEvents(0,100,$isAdmin,$userUId);

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

    } else if($requestTitle) {

        //$if string contain only digits
        //$get event data from db for requestTitle 

        $requestTitle = html_entity_decode(urldecode($requestTitle), ENT_QUOTES | ENT_HTML5);

        if(isset($_GET["visitorId"]) && !empty($_GET["visitorId"])){
            $visitorId = htmlspecialchars($_GET["visitorId"]);
            $events =  getEvent($requestTitle,$isAdmin,$userUId,$visitorId);
        }else{
            $events =  getEvent($requestTitle,$isAdmin,$userUId);
        }

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