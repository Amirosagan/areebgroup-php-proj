<?php
function uploadFile($file) {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    $uploadDir = "uploads/" . $extension;
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $destination = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'file_path' => $destination
        ];
    }

    return [
        'success' => false,
        'error' => 'Failed to upload file'
    ];
}
?>