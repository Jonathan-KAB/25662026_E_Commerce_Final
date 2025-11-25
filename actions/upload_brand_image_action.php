<?php
/**
 * Upload Brand Image Action
 * Handles image uploads for brands
 */

require_once __DIR__ . '/../classes/image_helper.php';
require_once __DIR__ . '/../controllers/brand_controller.php';
require_once __DIR__ . '/../settings/core.php';

session_start();
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    $response['message'] = 'Not authorized';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}
// Validate inputs
if (!isset($_POST['brand_id']) || !isset($_FILES['image'])) {
    $response['message'] = 'Missing brand ID or image file';
    echo json_encode($response);
    exit;
}

$brandId = (int)$_POST['brand_id'];
$userId = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;

if ($brandId <= 0 || $userId <= 0) {
    $response['message'] = 'Invalid brand ID or user ID';
    echo json_encode($response);
    exit;
}

// Upload image using helper class
$imageHelper = new ImageUploadHelper();
$uploadResult = $imageHelper->uploadBrandImage($_FILES['image'], $brandId, $userId);

if (!$uploadResult['success']) {
    $response['message'] = $uploadResult['message'];
    echo json_encode($response);
    exit;
}

// Update brand image in database using controller
$updateResult = update_brand_image_ctr($brandId, $uploadResult['path']);

if ($updateResult) {
    $response = [
        'status' => 'success',
        'message' => 'Brand image uploaded successfully',
        'path' => $uploadResult['path']
    ];
} else {
    $response['message'] = 'Failed to update brand image in database';
}

echo json_encode($response);
?>
