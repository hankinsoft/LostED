<?php

function convertToBytes($value) {
    $number = (int)$value;
    $lastChar = strtolower(substr($value, -1));
    switch($lastChar) {
        case 'g':
            $number *= 1024;
        case 'm':
            $number *= 1024;
        case 'k':
            $number *= 1024;
    }
    return $number;
}

function getMaxUploadSize() {
    $uploadMaxFilesize = ini_get('upload_max_filesize');
    $postMaxSize = ini_get('post_max_size');

    // Convert sizes to bytes for comparison
    $maxUpload = convertToBytes($uploadMaxFilesize);
    $maxPost = convertToBytes($postMaxSize);

    // Log each value separately
    echo "upload_max_filesize (in MB): " . round($maxUpload / 1048576, 2) . "\n";
    echo "post_max_size (in MB): " . round($maxPost / 1048576, 2) . "\n";

    // Determine the smaller of the two values as the effective maximum
    $maxFileSize = min($maxUpload, $maxPost);

    // Return the smaller value in megabytes
    return round($maxFileSize / 1048576, 2);
}

// Get and display the maximum file upload size
$maxUploadSize = getMaxUploadSize();