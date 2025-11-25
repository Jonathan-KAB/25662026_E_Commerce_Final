<?php
/**
 * Upload Product Image Action
 * Handles image uploads for products using ImageUploadHelper
 */

require_once __DIR__ . '/../classes/image_helper.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../settings/core.php';

require_once __DIR__ . '/../settings/core.php';
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

// Check if user is logged in and is admin or seller (roles 3 and 4)
if (!isLoggedIn() || (!isAdmin() && $_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4)) {
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
if (!isset($_POST['product_id']) || !isset($_FILES['image'])) {
    $response['message'] = 'Missing product ID or image file';
    echo json_encode($response);
    exit;
}

$productId = (int)$_POST['product_id'];
$userId = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;

if ($productId <= 0 || $userId <= 0) {
    $response['message'] = 'Invalid product ID or user ID';
    echo json_encode($response);
    exit;
}

// If seller, verify they own this product
if (!isAdmin()) {
    require_once __DIR__ . '/../classes/product_class.php';
    $product_obj = new Product();
    $existing_product = $product_obj->getProductById($productId);
    if (!$existing_product || $existing_product['seller_id'] != $_SESSION['customer_id']) {
        $response['message'] = 'You can only upload images for your own products';
        echo json_encode($response);
        exit;
    }
}

// Upload image using helper class
$imageHelper = new ImageUploadHelper();
$uploadResult = $imageHelper->uploadProductImage($_FILES['image'], $productId, $userId);

if (!$uploadResult['success']) {
    $response['message'] = $uploadResult['message'];
    echo json_encode($response);
    exit;
}

// Update product image in database
$updateResult = update_product_ctr($productId, ['product_image' => $uploadResult['path']]);

if ($updateResult) {
    $response = [
        'status' => 'success',
        'message' => 'Product image uploaded successfully',
        'path' => $uploadResult['path']
    ];
} else {
    $response['message'] = 'Failed to update product image in database';
}

echo json_encode($response);
?>

