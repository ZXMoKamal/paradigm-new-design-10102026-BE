<?php
//get thumb
function getEventThumb($id) {

    // Auto-detect base URL
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/paradigm/backend/paradigm-portfolio-backend/";

    // Build absolute path safely
    $relativePath = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "past events" . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . "thumb";

    $basePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);

    // If folder doesn't exist
    if (!$basePath) {
        return null;
    }

    // Allowed extensions
    $extensions = ['webp', 'jpg', 'jpeg', 'png'];

    foreach ($extensions as $ext) {

        $filePath = $basePath . DIRECTORY_SEPARATOR . "thumb." . $ext;

        if (file_exists($filePath)) {
            return $baseUrl . "storage/images/past events/" . $id . "/thumb/thumb." . $ext;
        }
    }

    return null;
}


function getEventImages($id) {

    // Auto-detect base URL
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/paradigm/backend/paradigm-portfolio-backend/";

    // Folder path (main event folder, NOT thumb)
    $relativePath = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "past events" . DIRECTORY_SEPARATOR . $id;

    $basePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);

    // If folder doesn't exist
    if (!$basePath) {
        return [];
    }

    // Allowed extensions
    $extensions = ['png', 'jpg', 'jpeg', 'webp'];

    $images = [];

    // Scan directory
    $files = scandir($basePath);

    foreach ($files as $file) {

        // Skip current & parent dirs + thumb folder
        if ($file === '.' || $file === '..' || $file === 'thumb') {
            continue;
        }

        // Get file extension
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($ext, $extensions)) {

            // Build URL (encode spaces!)
            $urlPath = "storage/images/past events/" . $id . "/" . $file;
            $images[] = $baseUrl . str_replace(' ', '%20', $urlPath);
        }
    }

    return $images;
}