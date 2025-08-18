<?php
// Debug information
error_log("compress-image.php: Script started");

require '../auth/middleware.php';
checkAccess(['Teacher']);

// Check if request is a POST request with a file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qrImage'])) {
    // Set content type to JSON
    header('Content-Type: application/json');

    // Debug information
    error_log("compress-image.php: File upload received: " . json_encode($_FILES['qrImage']));
    error_log("compress-image.php: File size: " . filesize($_FILES['qrImage']['tmp_name']) . " bytes");

    // Check for upload errors
    if ($_FILES['qrImage']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Upload failed with error code: ' . $_FILES['qrImage']['error'];
        error_log("compress-image.php: " . $errorMessage);
        echo json_encode(['success' => false, 'error' => $errorMessage]);
        exit;
    }

    try {
        // Make sure GD is available
        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagecreatetruecolor')) {
            error_log("compress-image.php: GD library functions not available");
            // Simple fallback - just copy the file if GD is not available
            $uploadDir = '../uploads/qr_temp/';
            if (!file_exists($uploadDir)) {
                if (!@mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Failed to create upload directory");
                }
            }

            $timestamp = time();
            $random = rand(1000, 9999);
            $filename = "qr_temp_{$timestamp}_{$random}.jpg";
            $compressedPath = $uploadDir . $filename;

            if (!@copy($_FILES['qrImage']['tmp_name'], $compressedPath)) {
                throw new Exception("Failed to copy uploaded file to: $compressedPath");
            }

            // A more robust URL construction that doesn't depend on specific path segments
            $baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $baseUrl .= "://{$_SERVER['HTTP_HOST']}";

            // Get the application root path by removing everything after /mcmod/
            $appPath = preg_replace('/\/mcmod\/.*$/', '/mcmod/', $_SERVER['REQUEST_URI']);
            $baseUrl .= $appPath;

            // Construct the final image URL
            $imageUrl = $baseUrl . 'uploads/qr_temp/' . $filename;

            error_log("compress-image.php: Using simple file copy fallback: $imageUrl");

            echo json_encode([
                'success' => true,
                'imageUrl' => $imageUrl,
                'width' => 0,
                'height' => 0,
                'note' => 'Using simple file copy (no compression)'
            ]);
            exit;
        }

        // Validate file size (max 10MB)
        if ($_FILES['qrImage']['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'File too large. Max size is 10MB.']);
            exit;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['qrImage']['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG and GIF are allowed.']);
            exit;
        }

        // Create upload directory if it doesn't exist
        $uploadDir = '../uploads/qr_temp/';
        if (!file_exists($uploadDir)) {
            // Add error handling for directory creation
            $dirCreated = @mkdir($uploadDir, 0777, true);
            if (!$dirCreated) {
                error_log("Failed to create directory: $uploadDir");
                echo json_encode([
                    'success' => false,
                    'error' => 'Server configuration error: Unable to create upload directory. Please contact administrator.'
                ]);
                exit;
            }
        } else {
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                @chmod($uploadDir, 0777);
                if (!is_writable($uploadDir)) {
                    error_log("Directory is not writable: $uploadDir");
                    echo json_encode([
                        'success' => false,
                        'error' => 'Server configuration error: Upload directory is not writable. Please contact administrator.'
                    ]);
                    exit;
                }
            }
        }

        // Clean up old temp files (older than 1 hour)
        $files = glob($uploadDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        if ($files === false) {
            error_log("Failed to list files in directory: $uploadDir");
        } else {
            $now = time();
            foreach ($files as $file) {
                if ($now - filemtime($file) > 3600) { // 3600 seconds = 1 hour
                    @unlink($file);
                }
            }
        }

        // Create a unique filename
        $timestamp = time();
        $random = rand(1000, 9999);
        $filename = "qr_temp_{$timestamp}_{$random}.jpg";
        $compressedPath = $uploadDir . $filename;

        // Compress the image based on type
        $imageInfo = getimagesize($_FILES['qrImage']['tmp_name']);
        if ($imageInfo === false) {
            echo json_encode(['success' => false, 'error' => 'Invalid image file.']);
            exit;
        }

        // Get max dimensions (400x400 is usually sufficient for QR codes)
        $maxWidth = 800;
        $maxHeight = 800;
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Calculate new dimensions, maintaining aspect ratio
        if ($width > $height && $width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval($height * ($maxWidth / $width));
        } elseif ($height > $maxHeight) {
            $newHeight = $maxHeight;
            $newWidth = intval($width * ($maxHeight / $height));
        } else {
            // No resizing needed
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create a new image with the new dimensions
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Process based on image type
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $srcImage = imagecreatefromjpeg($_FILES['qrImage']['tmp_name']);
                break;

            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($_FILES['qrImage']['tmp_name']);

                // Preserve transparency
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
                break;

            case IMAGETYPE_GIF:
                $srcImage = imagecreatefromgif($_FILES['qrImage']['tmp_name']);
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Unsupported image format.']);
                exit;
        }

        // Resize the image
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Apply specialized filters to improve QR code readability
        imagefilter($dstImage, IMG_FILTER_CONTRAST, 30); // Increase contrast for better QR detection

        // Apply additional processing specifically for QR codes
        // Sharpen the image to enhance edges (important for QR code recognition)
        $sharpen = array(
            array(-1, -1, -1),
            array(-1, 16, -1),
            array(-1, -1, -1)
        );
        $divisor = 8;
        $offset = 0;
        imageconvolution($dstImage, $sharpen, $divisor, $offset);

        // For grayscale images or low contrast scenarios
        if (imagecolorstotal($dstImage) < 256) {
            // Additional contrast adjustment for low-color images
            imagefilter($dstImage, IMG_FILTER_BRIGHTNESS, 10);
            imagefilter($dstImage, IMG_FILTER_CONTRAST, 20);
        }

        // Save as JPEG with 85% quality (good balance)
        $saveResult = imagejpeg($dstImage, $compressedPath, 85);
        if (!$saveResult) {
            throw new Exception("Failed to save compressed image to: $compressedPath");
        }

        // Clean up
        imagedestroy($srcImage);
        imagedestroy($dstImage);

        // Verify the file was created successfully
        if (!file_exists($compressedPath)) {
            throw new Exception("Compressed file was not created: $compressedPath");
        }

        // Construct URL to the compressed image
        $baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $baseUrl .= "://{$_SERVER['HTTP_HOST']}";

        // Get the application root path by removing everything after /mcmod/
        $appPath = preg_replace('/\/mcmod\/.*$/', '/mcmod/', $_SERVER['REQUEST_URI']);
        $baseUrl .= $appPath;

        // Construct the final image URL
        $imageUrl = $baseUrl . 'uploads/qr_temp/' . $filename;

        // Debug information
        error_log("compress-image.php: Compression successful, returning URL: $imageUrl");

        // Return success response with image URL
        echo json_encode([
            'success' => true,
            'imageUrl' => $imageUrl,
            'width' => $newWidth,
            'height' => $newHeight
        ]);
        exit;
    } catch (Exception $e) {
        error_log("compress-image.php: Exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
} else {
    // Return error if not a POST request with file
    error_log("compress-image.php: Invalid request. Must be POST with file.");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request. Must be POST with file.']);
    exit;
}
