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



// function saveEventImages($eventId, $thumb, $sliderImages){
//     $allowedExtensions = [
//         "jpg",
//         "jpeg",
//         "png",
//         "webp"
//     ];

//     $allowedMimeTypes = [
//         "image/jpeg",
//         "image/png",
//         "image/webp"
//     ];


//     // ---------------------------------------------------------
//     // Event storage paths
//     // ---------------------------------------------------------

//     $storagePath =
//         __DIR__ .
//         DIRECTORY_SEPARATOR .
//         ".." .
//         DIRECTORY_SEPARATOR .
//         ".." .
//         DIRECTORY_SEPARATOR .
//         "storage" .
//         DIRECTORY_SEPARATOR .
//         "images" .
//         DIRECTORY_SEPARATOR .
//         "past events" .
//         DIRECTORY_SEPARATOR .
//         $eventId;

//     $thumbPath =
//         $storagePath .
//         DIRECTORY_SEPARATOR .
//         "thumb";


//     // ---------------------------------------------------------
//     // Create directories
//     // ---------------------------------------------------------

//     if (!is_dir($storagePath)) {

//         if (!mkdir($storagePath, 0755, true)) {

//             return [
//                 "error" => "Failed to create event storage directory"
//             ];
//         }
//     }


//     if (!is_dir($thumbPath)) {

//         if (!mkdir($thumbPath, 0755, true)) {

//             return [
//                 "error" => "Failed to create thumbnail directory"
//             ];
//         }
//     }


//     // ---------------------------------------------------------
//     // Validate thumbnail
//     // ---------------------------------------------------------

//     $thumbExtension = strtolower(
//         pathinfo($thumb["name"], PATHINFO_EXTENSION)
//     );

//     $thumbMime = mime_content_type($thumb["tmp_name"]);

//     if (
//         !in_array($thumbExtension, $allowedExtensions, true) ||
//         !in_array($thumbMime, $allowedMimeTypes, true)
//     ) {

//         return [
//             "error" => "Invalid thumbnail image type"
//         ];
//     }


//     // ---------------------------------------------------------
//     // Save thumbnail
//     // ---------------------------------------------------------

//     $thumbFileName =
//         "thumb." .
//         $thumbExtension;

//     $thumbDestination =
//         $thumbPath .
//         DIRECTORY_SEPARATOR .
//         $thumbFileName;


//     if (!move_uploaded_file(
//         $thumb["tmp_name"],
//         $thumbDestination
//     )) {

//         return [
//             "error" => "Failed to save thumbnail"
//         ];
//     }


//     // ---------------------------------------------------------
//     // Save slider images
//     // ---------------------------------------------------------

//     $savedSliderImages = [];

//     $sliderCount = count($sliderImages["name"]);


//     for ($i = 0; $i < $sliderCount; $i++) {

//         if (
//             $sliderImages["error"][$i]
//             !== UPLOAD_ERR_OK
//         ) {
//             continue;
//         }


//         $tmpName =
//             $sliderImages["tmp_name"][$i];

//         $originalName =
//             $sliderImages["name"][$i];


//         $extension = strtolower(
//             pathinfo($originalName, PATHINFO_EXTENSION)
//         );


//         $mimeType =
//             mime_content_type($tmpName);


//         // Validate image
//         if (
//             !in_array($extension, $allowedExtensions, true) ||
//             !in_array($mimeType, $allowedMimeTypes, true)
//         ) {
//             continue;
//         }


//         // Generate filename
//         $fileName =
//             "image-" .
//             ($i + 1) .
//             "." .
//             $extension;


//         $destination =
//             $storagePath .
//             DIRECTORY_SEPARATOR .
//             $fileName;


//         if (move_uploaded_file(
//             $tmpName,
//             $destination
//         )) {

//             $savedSliderImages[] = $fileName;
//         }
//     }


//     return [
//         "thumbnail" => $thumbFileName,
//         "sliderImages" => $savedSliderImages
//     ];
// }
function saveEventImages($eventId, $thumb, $sliderImages)
{
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
    // Event storage paths
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
        "past events" .
        DIRECTORY_SEPARATOR .
        $eventId;

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
                "error" => "Failed to create event storage directory"
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

    if (
        !isset($thumb["tmp_name"]) ||
        !is_uploaded_file($thumb["tmp_name"])
    ) {

        return [
            "error" => "Invalid thumbnail upload"
        ];
    }


    $thumbExtension = strtolower(
        pathinfo($thumb["name"], PATHINFO_EXTENSION)
    );

    $thumbMime = mime_content_type(
        $thumb["tmp_name"]
    );


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


    // ---------------------------------------------------------
    // Validate slider images
    // ---------------------------------------------------------

    if (
        !isset($sliderImages["name"]) ||
        !is_array($sliderImages["name"])
    ) {

        return [
            "error" => "Slider images are required"
        ];
    }


    // ---------------------------------------------------------
    // Save slider images
    // ---------------------------------------------------------

    $savedSliderImages = [];

    $sliderCount = count(
        $sliderImages["name"]
    );


    for ($i = 0; $i < $sliderCount; $i++) {

        // -----------------------------------------------------
        // Check upload
        // -----------------------------------------------------

        if (
            !isset($sliderImages["error"][$i]) ||
            $sliderImages["error"][$i] !== UPLOAD_ERR_OK
        ) {
            continue;
        }


        $tmpName =
            $sliderImages["tmp_name"][$i];


        $originalName =
            $sliderImages["name"][$i];


        if (
            !is_uploaded_file($tmpName)
        ) {
            continue;
        }


        // -----------------------------------------------------
        // Get extension
        // -----------------------------------------------------

        $extension = strtolower(
            pathinfo(
                $originalName,
                PATHINFO_EXTENSION
            )
        );


        // -----------------------------------------------------
        // Get MIME type
        // -----------------------------------------------------

        $mimeType =
            mime_content_type($tmpName);


        // -----------------------------------------------------
        // Validate image
        // -----------------------------------------------------

        if (
            !in_array(
                $extension,
                $allowedExtensions,
                true
            ) ||
            !in_array(
                $mimeType,
                $allowedMimeTypes,
                true
            )
        ) {
            continue;
        }


        // -----------------------------------------------------
        // Generate unique filename
        // -----------------------------------------------------

        $fileName =
            "image-" .
            bin2hex(random_bytes(16)) .
            "." .
            $extension;


        // -----------------------------------------------------
        // Destination
        // -----------------------------------------------------

        $destination =
            $storagePath .
            DIRECTORY_SEPARATOR .
            $fileName;


        // -----------------------------------------------------
        // Save image
        // -----------------------------------------------------

        if (
            move_uploaded_file(
                $tmpName,
                $destination
            )
        ) {

            $savedSliderImages[] = $fileName;
        }
    }


    // ---------------------------------------------------------
    // Return saved images
    // ---------------------------------------------------------

    return [
        "thumbnail" => $thumbFileName,
        "sliderImages" => $savedSliderImages
    ];
}

function updateEventImages(
    $eventId,
    $newThumb,
    $existingSliderImages,
    $newSliderImages
) {
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
    // Event storage path
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
        "past events" .
        DIRECTORY_SEPARATOR .
        $eventId;


    $thumbPath =
        $storagePath .
        DIRECTORY_SEPARATOR .
        "thumb";


    // ---------------------------------------------------------
    // Check event storage directory
    // ---------------------------------------------------------

    if (!is_dir($storagePath)) {

        if (!mkdir($storagePath, 0755, true)) {

            return [
                "error" => "Failed to create event storage directory"
            ];
        }
    }


    // ---------------------------------------------------------
    // Check thumbnail directory
    // ---------------------------------------------------------

    if (!is_dir($thumbPath)) {

        if (!mkdir($thumbPath, 0755, true)) {

            return [
                "error" => "Failed to create thumbnail directory"
            ];
        }
    }


    // =========================================================
    // THUMBNAIL
    // =========================================================


    // ---------------------------------------------------------
    // Find current thumbnail
    // ---------------------------------------------------------

    $currentThumbnail = null;


    $thumbFiles = glob(
        $thumbPath .
        DIRECTORY_SEPARATOR .
        "thumb.*"
    );


    if ($thumbFiles !== false && count($thumbFiles) > 0) {

        $currentThumbnail =
            basename($thumbFiles[0]);
    }


    // ---------------------------------------------------------
    // If new thumbnail was uploaded
    // ---------------------------------------------------------

    if ($newThumb !== null) {

        if (
            !isset($newThumb["tmp_name"]) ||
            !is_uploaded_file($newThumb["tmp_name"])
        ) {

            return [
                "error" => "Invalid thumbnail upload"
            ];
        }


        $thumbExtension = strtolower(
            pathinfo(
                $newThumb["name"],
                PATHINFO_EXTENSION
            )
        );


        $thumbMime = mime_content_type(
            $newThumb["tmp_name"]
        );


        // -----------------------------------------------------
        // Validate thumbnail
        // -----------------------------------------------------

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


        // -----------------------------------------------------
        // Delete old thumbnail(s)
        // -----------------------------------------------------

        foreach ($thumbFiles as $oldThumb) {

            if (is_file($oldThumb)) {

                if (!unlink($oldThumb)) {

                    return [
                        "error" => "Failed to remove old thumbnail"
                    ];
                }
            }
        }


        // -----------------------------------------------------
        // New thumbnail filename
        // -----------------------------------------------------

        $thumbFileName =
            "thumb." .
            $thumbExtension;


        $thumbDestination =
            $thumbPath .
            DIRECTORY_SEPARATOR .
            $thumbFileName;


        // -----------------------------------------------------
        // Save new thumbnail
        // -----------------------------------------------------

        if (!move_uploaded_file(
            $newThumb["tmp_name"],
            $thumbDestination
        )) {

            return [
                "error" => "Failed to save new thumbnail"
            ];
        }


        $currentThumbnail =
            $thumbFileName;
    }


    // =========================================================
    // SLIDER IMAGES
    // =========================================================


    // ---------------------------------------------------------
    // Clean keep-list
    // ---------------------------------------------------------

    $keepSliderImages = [];


    foreach ($existingSliderImages as $fileName) {

        if (!is_string($fileName)) {
            continue;
        }


        $fileName =
            basename($fileName);


        // Only allow image files

        $extension =
            strtolower(
                pathinfo(
                    $fileName,
                    PATHINFO_EXTENSION
                )
            );


        if (
            !in_array(
                $extension,
                $allowedExtensions,
                true
            )
        ) {
            continue;
        }


        $keepSliderImages[] =
            $fileName;
    }


    // Remove duplicates

    $keepSliderImages =
        array_unique($keepSliderImages);


    // =========================================================
    // Delete slider images that are no longer kept
    // =========================================================


    $allFiles = glob(
        $storagePath .
        DIRECTORY_SEPARATOR .
        "image-*"
    );


    if ($allFiles !== false) {

        foreach ($allFiles as $filePath) {

            if (!is_file($filePath)) {
                continue;
            }


            $fileName =
                basename($filePath);


            if (
                !in_array(
                    $fileName,
                    $keepSliderImages,
                    true
                )
            ) {

                if (!unlink($filePath)) {

                    return [
                        "error" =>
                            "Failed to remove slider image: " .
                            $fileName
                    ];
                }
            }
        }
    }


    // =========================================================
    // Save new slider images
    // =========================================================


    if (
        $newSliderImages !== null &&
        isset($newSliderImages["name"]) &&
        is_array($newSliderImages["name"])
    ) {

        $sliderCount =
            count($newSliderImages["name"]);


        for ($i = 0; $i < $sliderCount; $i++) {

            // -------------------------------------------------
            // Upload error
            // -------------------------------------------------

            if (
                !isset($newSliderImages["error"][$i]) ||
                $newSliderImages["error"][$i]
                    !== UPLOAD_ERR_OK
            ) {
                continue;
            }


            $tmpName =
                $newSliderImages["tmp_name"][$i];


            $originalName =
                $newSliderImages["name"][$i];


            // -------------------------------------------------
            // Validate upload
            // -------------------------------------------------

            if (
                !is_uploaded_file($tmpName)
            ) {
                continue;
            }


            // -------------------------------------------------
            // Extension
            // -------------------------------------------------

            $extension =
                strtolower(
                    pathinfo(
                        $originalName,
                        PATHINFO_EXTENSION
                    )
                );


            // -------------------------------------------------
            // MIME
            // -------------------------------------------------

            $mimeType =
                mime_content_type($tmpName);


            // -------------------------------------------------
            // Validate image
            // -------------------------------------------------

            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                ) ||
                !in_array(
                    $mimeType,
                    $allowedMimeTypes,
                    true
                )
            ) {
                continue;
            }


            // -------------------------------------------------
            // Generate unique filename
            // -------------------------------------------------

            $fileName =
                "image-" .
                bin2hex(
                    random_bytes(16)
                ) .
                "." .
                $extension;


            // -------------------------------------------------
            // Destination
            // -------------------------------------------------

            $destination =
                $storagePath .
                DIRECTORY_SEPARATOR .
                $fileName;


            // -------------------------------------------------
            // Save
            // -------------------------------------------------

            if (
                move_uploaded_file(
                    $tmpName,
                    $destination
                )
            ) {

                $keepSliderImages[] =
                    $fileName;
            }
        }
    }


    // =========================================================
    // Get final slider image list
    // =========================================================


    $finalSliderImages = [];


    $finalFiles = glob(
        $storagePath .
        DIRECTORY_SEPARATOR .
        "image-*"
    );


    if ($finalFiles !== false) {

        foreach ($finalFiles as $filePath) {

            if (!is_file($filePath)) {
                continue;
            }


            $finalSliderImages[] =
                basename($filePath);
        }
    }


    // ---------------------------------------------------------
    // Return
    // ---------------------------------------------------------

    return [
        "thumbnail" => $currentThumbnail,

        "sliderImages" => $finalSliderImages
    ];
}


function deleteEventStorage($eventId)
{
    $eventId = (int)$eventId;

    if ($eventId <= 0) {
        return [
            "error" => "Invalid event ID"
        ];
    }


    // Build path
    $eventPath =
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
        "past events" .
        DIRECTORY_SEPARATOR .
        $eventId;


    // Convert to absolute normalized path
    $eventPath = realpath($eventPath);


    // Folder doesn't exist
    if ($eventPath === false) {

        return [
            "success" => true,
            "message" => "Event storage folder does not exist"
        ];
    }


    // Make sure we are deleting ONLY inside storage/events
    $eventsRoot =
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
            "past events"
        );


    if ($eventsRoot === false) {

        return [
            "error" => "Events storage directory not found"
        ];
    }


    // Security check
    if (
        strpos(
            $eventPath,
            $eventsRoot . DIRECTORY_SEPARATOR
        ) !== 0
    ) {

        return [
            "error" => "Invalid event storage path"
        ];
    }


    // Delete directory recursively
    if (!deleteEventDirectory($eventPath)) {

        return [
            "error" => "Failed to delete event storage"
        ];
    }


    return [
        "success" => true
    ];
}


/**
 * Delete directory and all contents
 */
function deleteEventDirectory($directory)
{
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

            if (!deleteEventDirectory($path)) {
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