<?php
include ("../controllers/db/blogs.php");
include ("../controllers/storage/blogs.php");
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
        
        //$get all blog data from db

        $blogs =  getAllBlogs(0,20,$isAdmin,$userUId);

        if(array_key_exists("error",$blogs)){
            http_response_code(404);
            echo json_encode($blogs);
            die();
        }else{
            http_response_code(200);
            //$get blog images

            foreach ($blogs as &$blog) {

                // Get thumb URL using your function
                $thumb = getBlogThumb($blog['id']);

                // Add it to the same array
                $blog['thumb'] = $thumb;
            }

            echo json_encode($blogs,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

    } else if($requestTitle) {

        //$if string contain only digits
        //$get blog data from db for requestTitle 

        $requestTitle = html_entity_decode(urldecode($requestTitle), ENT_QUOTES | ENT_HTML5);

        if(isset($_GET["visitorId"]) && !empty($_GET["visitorId"])){
            $visitorId = htmlspecialchars($_GET["visitorId"]);
            $blogs =  getBlog($requestTitle,$isAdmin,$userUId,$visitorId);
        }else{
            $blogs =  getBlog($requestTitle,$isAdmin,$userUId);
        }

        if(array_key_exists("error",$blogs)){
            http_response_code(404);
            echo json_encode($blogs);
            die();
        }else{
            http_response_code(200);
            //$get events images

            $thumb = getBlogThumb($blogs['id']);
            $blogs['thumb'] = $thumb;

            // $images = getEventImages($blogs['id']);
            // $blogs['images'] = $images;

            echo json_encode($blogs,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
            
    }

}