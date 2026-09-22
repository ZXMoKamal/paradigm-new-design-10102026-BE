<?php

require_once(__DIR__."/../db/ACL.php");
    include(__DIR__."/../../configs/connectDB.php");

//get thumb
function getBlogThumb($id) {

    // Auto-detect base URL
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/paradigm/backend/paradigm-portfolio-backend/";

    // Build absolute path safely
    $relativePath = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "blog" . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . "thumb";

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
            return $baseUrl . "storage/images/blog/" . $id . "/thumb/thumb." . $ext;
        }
    }

    return null;
}

#regon
    // function getBlogImages($id) {

    //     // Auto-detect base URL
    //     $baseUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/paradigm/backend/paradigm-portfolio-backend/";

    //     // Folder path (main event folder, NOT thumb)
    //     $relativePath = ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "past events" . DIRECTORY_SEPARATOR . $id;

    //     $basePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $relativePath);

    //     // If folder doesn't exist
    //     if (!$basePath) {
    //         return [];
    //     }

    //     // Allowed extensions
    //     $extensions = ['png', 'jpg', 'jpeg', 'webp'];

    //     $images = [];

    //     // Scan directory
    //     $files = scandir($basePath);

    //     foreach ($files as $file) {

    //         // Skip current & parent dirs + thumb folder
    //         if ($file === '.' || $file === '..' || $file === 'thumb') {
    //             continue;
    //         }

    //         // Get file extension
    //         $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    //         if (in_array($ext, $extensions)) {

    //             // Build URL (encode spaces!)
    //             $urlPath = "storage/images/past events/" . $id . "/" . $file;
    //             $images[] = $baseUrl . str_replace(' ', '%20', $urlPath);
    //         }
    //     }

    //     return $images;
    // }
#endregon

function updateBlogImages($blogId, $newThumb,$uid)
{
 global $conn;
    // if($isAdmin){
        /*
        * Get admin database ID using Firebase UID
        */
        $adminSql = "SELECT id FROM admins WHERE fbuid = ? LIMIT 1";

        $adminStmt = $conn->prepare($adminSql);

        if (!$adminStmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare admin query"
            ];
        }

        $adminStmt->bind_param(
            "s",
            $uid
        );

        if (!$adminStmt->execute()) {
            error_log($adminStmt->error);

            $adminStmt->close();

            return [
                "error" => "Failed to find admin"
            ];
        }

        $adminResult = $adminStmt->get_result();

        if ($adminResult->num_rows === 0) {
            $adminStmt->close();

            return [
                "error" => "Admin not found"
            ];
        }

        $admin = $adminResult->fetch_assoc();
        $ACLresult= checkAdminACL($admin["id"],"blog","edit");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have edit access for blog"
            ]);

            exit;

        }
    // }



    $allowedExtensions = [
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];

    $allowedMimeTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];


    // ---------------------------------------------------------
    // Blog storage paths
    // ---------------------------------------------------------

    $storagePath =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        ".." .
        DIRECTORY_SEPARATOR .
        ".." .
        DIRECTORY_SEPARATOR .
        "storage" .
        DIRECTORY_SEPARATOR .
        "images" .
        DIRECTORY_SEPARATOR .
        "blog" .
        DIRECTORY_SEPARATOR .
        $blogId;


    $thumbPath =
        $storagePath .
        DIRECTORY_SEPARATOR .
        "thumb";


    // ---------------------------------------------------------
    // Make sure directories exist
    // ---------------------------------------------------------

    if (!is_dir($storagePath)) {

        if (!mkdir($storagePath, 0755, true)) {

            return [
                "error" => "Failed to create blog storage directory"
            ];
        }
    }


    if (!is_dir($thumbPath)) {

        if (!mkdir($thumbPath, 0755, true)) {

            return [
                "error" => "Failed to create thumbnail directory"
            ];
        }
    }


    // ---------------------------------------------------------
    // Find current thumbnail
    // ---------------------------------------------------------

    $currentThumbs = glob(
        $thumbPath .
        DIRECTORY_SEPARATOR .
        "thumb.*"
    );


    // ---------------------------------------------------------
    // No new thumbnail
    // ---------------------------------------------------------
    //
    // Keep the existing thumbnail.
    //

    if ($newThumb === null) {

        if (!empty($currentThumbs)) {

            $currentThumb = basename($currentThumbs[0]);

            return [
                "thumbnail" => $currentThumb
            ];
        }


        // No old thumbnail exists either

        return [
            "thumbnail" => null
        ];
    }


    // ---------------------------------------------------------
    // Validate new thumbnail
    // ---------------------------------------------------------

    $thumbExtension = strtolower(
        pathinfo(
            $newThumb["name"],
            PATHINFO_EXTENSION
        )
    );


    $thumbMime = mime_content_type(
        $newThumb["tmp_name"]
    );


    if (
        !in_array(
            $thumbExtension,
            $allowedExtensions,
            true
        ) ||
        !in_array(
            $thumbMime,
            $allowedMimeTypes,
            true
        )
    ) {

        return [
            "error" => "Invalid thumbnail image type"
        ];
    }


    // ---------------------------------------------------------
    // Delete old thumbnail(s)
    // ---------------------------------------------------------

    foreach ($currentThumbs as $oldThumb) {

        if (is_file($oldThumb)) {

            if (!unlink($oldThumb)) {

                return [
                    "error" => "Failed to remove old thumbnail"
                ];
            }
        }
    }


    // ---------------------------------------------------------
    // Save new thumbnail
    // ---------------------------------------------------------

    $thumbFileName =
        "thumb." .
        $thumbExtension;


    $thumbDestination =
        $thumbPath .
        DIRECTORY_SEPARATOR .
        $thumbFileName;


    if (!move_uploaded_file(
        $newThumb["tmp_name"],
        $thumbDestination
    )) {

        return [
            "error" => "Failed to save new thumbnail"
        ];
    }


    // ---------------------------------------------------------
    // Return thumbnail
    // ---------------------------------------------------------

    return [
        "thumbnail" => $thumbFileName
    ];
}


function saveBlogImages($blogId, $thumb, $uid){
  global $conn;
        // if($isAdmin){
        /*
        * Get admin database ID using Firebase UID
        */
        $adminSql = "SELECT id FROM admins WHERE fbuid = ? LIMIT 1";

        $adminStmt = $conn->prepare($adminSql);

        if (!$adminStmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare admin query"
            ];
        }

        $adminStmt->bind_param(
            "s",
            $uid
        );

        if (!$adminStmt->execute()) {
            error_log($adminStmt->error);

            $adminStmt->close();

            return [
                "error" => "Failed to find admin"
            ];
        }

        $adminResult = $adminStmt->get_result();

        if ($adminResult->num_rows === 0) {
            $adminStmt->close();

            return [
                "error" => "Admin not found"
            ];
        }

        $admin = $adminResult->fetch_assoc();
        $ACLresult= checkAdminACL($admin["id"],"blog","create");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have create access for blog"
            ]);

            exit;

        }
    // }



    $allowedExtensions = [
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];

    $allowedMimeTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];


    // ---------------------------------------------------------
    // Blog storage paths
    // ---------------------------------------------------------

    $storagePath =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        ".." .
        DIRECTORY_SEPARATOR .
        ".." .
        DIRECTORY_SEPARATOR .
        "storage" .
        DIRECTORY_SEPARATOR .
        "images" .
        DIRECTORY_SEPARATOR .
        "blog" .
        DIRECTORY_SEPARATOR .
        $blogId;

    $thumbPath =
        $storagePath .
        DIRECTORY_SEPARATOR .
        "thumb";


    // ---------------------------------------------------------
    // Create directories
    // ---------------------------------------------------------

    if (!is_dir($storagePath)) {

        if (!mkdir($storagePath, 0755, true)) {

            return [
                "error" => "Failed to create blog storage directory"
            ];
        }
    }


    if (!is_dir($thumbPath)) {

        if (!mkdir($thumbPath, 0755, true)) {

            return [
                "error" => "Failed to create thumbnail directory"
            ];
        }
    }


    // ---------------------------------------------------------
    // Validate thumbnail
    // ---------------------------------------------------------

    $thumbExtension = strtolower(
        pathinfo($thumb["name"], PATHINFO_EXTENSION)
    );

    $thumbMime = mime_content_type($thumb["tmp_name"]);

    if (
        !in_array($thumbExtension, $allowedExtensions, true) ||
        !in_array($thumbMime, $allowedMimeTypes, true)
    ) {

        return [
            "error" => "Invalid thumbnail image type"
        ];
    }


    // ---------------------------------------------------------
    // Save thumbnail
    // ---------------------------------------------------------

    $thumbFileName =
        "thumb." .
        $thumbExtension;

    $thumbDestination =
        $thumbPath .
        DIRECTORY_SEPARATOR .
        $thumbFileName;


    if (!move_uploaded_file(
        $thumb["tmp_name"],
        $thumbDestination
    )) {

        return [
            "error" => "Failed to save thumbnail"
        ];
    }


    return [
        "thumbnail" => $thumbFileName
    ];
}




function deleteBlogStorage($blogId, $uid)
{
  global $conn;


    // if($isAdmin){
        /*
        * Get admin database ID using Firebase UID
        */
        $adminSql = "SELECT id FROM admins WHERE fbuid = ? LIMIT 1";

        $adminStmt = $conn->prepare($adminSql);

        if (!$adminStmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare admin query"
            ];
        }

        $adminStmt->bind_param(
            "s",
            $uid
        );

        if (!$adminStmt->execute()) {
            error_log($adminStmt->error);

            $adminStmt->close();

            return [
                "error" => "Failed to find admin"
            ];
        }

        $adminResult = $adminStmt->get_result();

        if ($adminResult->num_rows === 0) {
            $adminStmt->close();

            return [
                "error" => "Admin not found"
            ];
        }

        $admin = $adminResult->fetch_assoc();
        $ACLresult= checkAdminACL($admin["id"],"blog","delete");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have delete access for blog"
            ]);

            exit;

        }
    // }




    $blogId = (int)$blogId;

    if ($blogId <= 0) {
        return [
            "error" => "Invalid blog ID"
        ];
    }


    // Build path
    $blogPath =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        ".." .
        DIRECTORY_SEPARATOR .
        ".." .
        DIRECTORY_SEPARATOR .
        "storage" .
        DIRECTORY_SEPARATOR .
        "images" .
        DIRECTORY_SEPARATOR .
        "blog" .
        DIRECTORY_SEPARATOR .
        $blogId;


    // Convert to absolute normalized path
    $blogPath = realpath($blogPath);


    // Folder doesn't exist
    if ($blogPath === false) {

        return [
            "success" => true,
            "message" => "Blog storage folder does not exist"
        ];
    }


    // Make sure we are deleting ONLY inside storage/events
    $blogsRoot =
        realpath(
            __DIR__ .
            DIRECTORY_SEPARATOR .
            ".." .
            DIRECTORY_SEPARATOR .
            ".." .
            DIRECTORY_SEPARATOR .
            "storage" .
            DIRECTORY_SEPARATOR .
            "images" .
            DIRECTORY_SEPARATOR .
            "blog"
        );


    if ($blogsRoot === false) {

        return [
            "error" => "Blogs storage directory not found"
        ];
    }


    // Security check
    if (
        strpos(
            $blogPath,
            $blogsRoot . DIRECTORY_SEPARATOR
        ) !== 0
    ) {

        return [
            "error" => "Invalid blogs storage path"
        ];
    }


    // Delete directory recursively
    if (!deleteBlogDirectory($blogPath,$uid)) {

        return [
            "error" => "Failed to delete blog storage"
        ];
    }


    return [
        "success" => true
    ];
}


/**
 * Delete directory and all contents
 */
function deleteBlogDirectory($directory, $uid)
{
  global $conn;

    // if($isAdmin){
        /*
        * Get admin database ID using Firebase UID
        */
        $adminSql = "SELECT id FROM admins WHERE fbuid = ? LIMIT 1";

        $adminStmt = $conn->prepare($adminSql);

        if (!$adminStmt) {
            error_log($conn->error);

            return [
                "error" => "Failed to prepare admin query"
            ];
        }

        $adminStmt->bind_param(
            "s",
            $uid
        );

        if (!$adminStmt->execute()) {
            error_log($adminStmt->error);

            $adminStmt->close();

            return [
                "error" => "Failed to find admin"
            ];
        }

        $adminResult = $adminStmt->get_result();

        if ($adminResult->num_rows === 0) {
            $adminStmt->close();

            return [
                "error" => "Admin not found"
            ];
        }

        $admin = $adminResult->fetch_assoc();
        $ACLresult= checkAdminACL($admin["id"],"blog","delete");
        if(!($ACLresult["success"] && $ACLresult["allowed"])){

            http_response_code(403);

            echo json_encode([
                "error" => "You do not have delete access for blog"
            ]);

            exit;

        }
    // }





    if (!is_dir($directory)) {
        return false;
    }


    $items = scandir($directory);

    if ($items === false) {
        return false;
    }


    foreach ($items as $item) {

        if (
            $item === "." ||
            $item === ".."
        ) {
            continue;
        }


        $path =
            $directory .
            DIRECTORY_SEPARATOR .
            $item;


        if (is_dir($path)) {

            if (!deleteBlogDirectory($path)) {
                return false;
            }

        } else {

            if (!unlink($path)) {
                return false;
            }
        }
    }


    return rmdir($directory);
}