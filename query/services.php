<?php
// include ("../controllers/db/services.php");
// include ("../configs/header.php");
// require_once("../controllers/auth/auth.php");


// if ($_SERVER["REQUEST_METHOD"] == "GET") {

//     $user = verifyFirebaseToken(false);

//     $isAdmin = false;
//     $userUId = null;

//     if ($user && $user["admin"] === true) {
//         $isAdmin = true;
//         $userUId = $user["uid"];
//     }

//     $requestTitle = array_key_exists("requestTitle", $_GET)?htmlspecialchars($_GET['requestTitle']):"all";

//     if (empty($requestTitle) || $requestTitle=="all") {
        
//         //$get all services data from db

//         $services =  getAllServices($isAdmin,$userUId);


//     } else if($requestTitle) {

//         //$if string contain only digits
//         //$get service data from db for id 
//         $requestTitle = html_entity_decode(urldecode($requestTitle), ENT_QUOTES | ENT_HTML5);
//         if(isset($_GET["visitorId"]) && !empty($_GET["visitorId"])){
//             $visitorId = htmlspecialchars($_GET["visitorId"]);
//             $services =  getService($requestTitle,$isAdmin,$userUId,$visitorId);
//         }else{
//             $services =  getService($requestTitle,$isAdmin,$userUId);
//         }
//     }
        
//     if(array_key_exists("error",$services)){
//         http_response_code(404);
//     }else{
//         http_response_code(200);
//     }

//     echo json_encode($services);

// }





include("../controllers/db/services.php");
include("../controllers/storage/services.php"); // Include getServiceThumb()
include("../configs/header.php");
require_once("../controllers/auth/auth.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $user = verifyFirebaseToken(false);

    $isAdmin = false;
    $userUId = null;

    if ($user && $user["admin"] === true) {
        $isAdmin = true;
        $userUId = $user["uid"];
    }

    $requestTitle = array_key_exists("requestTitle", $_GET)
        ? htmlspecialchars($_GET['requestTitle'])
        : "all";

    if (empty($requestTitle) || $requestTitle == "all") {

        // Get all services
        $services = getAllServices($isAdmin, $userUId);

        if (!array_key_exists("error", $services)) {

            // Add thumbnail / icon URL to each service
            foreach ($services as &$service) {
                $service['thumb'] = getServiceThumb($service['id']);
                $service['icon'] = getServiceIcon($service['id']);
            }
            unset($service);
        }

    } else {

        // Get requested service
        $requestTitle = html_entity_decode(
            urldecode($requestTitle),
            ENT_QUOTES | ENT_HTML5
        );

        if (isset($_GET["visitorId"]) && !empty($_GET["visitorId"])) {
            $visitorId = htmlspecialchars($_GET["visitorId"]);
            $services = getService(
                $requestTitle,
                $isAdmin,
                $userUId,
                $visitorId
            );
        } else {
            $services = getService(
                $requestTitle,
                $isAdmin,
                $userUId
            );
        }

        if (!array_key_exists("error", $services)) {

            // Add thumbnail / icon URL to the requested service
            $services['thumb'] = getServiceThumb($services['id']);
            $services['icon'] = getServiceIcon($services['id']);
        }
    }

    if (array_key_exists("error", $services)) {
        http_response_code(404);
    } else {
        http_response_code(200);
    }

    echo json_encode(
        $services,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
}
