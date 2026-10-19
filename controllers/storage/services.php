<?php
require_once(__DIR__."/../db/ACL.php");
    include(__DIR__."/../../configs/connectDB.php");

//get thumb
function getServiceThumb($id) {

    // Auto-detect base URL
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/paradigm/backend/paradigm-portfolio-backend/";

    // Build absolute path safely
    $relativePath = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "services" . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . "thumb";

    $basePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);

    // If folder doesn't exist
    if (!$basePath) {
        return null;
    }

    // Allowed extensions
    $extensions = ['webp', 'jpg', 'jpeg', 'png', 'svg'];

    foreach ($extensions as $ext) {

        $filePath = $basePath . DIRECTORY_SEPARATOR . "thumb." . $ext;

        if (file_exists($filePath)) {
            return $baseUrl . "storage/images/services/" . $id . "/thumb/thumb." . $ext;
        }
    }

    return null;
}