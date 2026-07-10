<?php

header("Content-Type: application/json");

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

// Get values
$image = $data['image'] ?? '';
$user_id = $data['user_id'] ?? '0';
$order_id = $data['order_id'] ?? '0';

// Validate image
if (empty($image)) {
    echo json_encode([
        "status" => "failed",
        "message" => "No image received"
    ]);
    exit;
}

// Remove base64 header if present
$image = str_replace('data:image/jpeg;base64,', '', $image);
$image = str_replace(' ', '+', $image);

// Decode image
$imageData = base64_decode($image);

if ($imageData === false) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid base64 image"
    ]);
    exit;
}

// Create uploads folder if not exists
if (!file_exists("uploads")) {
    mkdir("uploads", 0777, true);
}

// Generate unique filename
$filename = "uploads/face_" . $user_id . "_" . time() . ".jpg";

// Save image
$saved = file_put_contents($filename, $imageData);

if ($saved) {

    echo json_encode([
        "status" => "success",
        "message" => "Face uploaded successfully",
        "image_url" => "https://paysmallsmall.org/" . $filename
    ]);

} else {

    echo json_encode([
        "status" => "failed",
        "message" => "Failed to save image"
    ]);
}