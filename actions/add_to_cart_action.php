<?php
/**
 * Add to Cart Action
 * Handles adding items to the cart
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    $response = [
        'status' => 'error',
        'message' => 'Please login to add items to cart',
        'redirect' => '../login/login.php'
    ];
    echo json_encode($response);
    exit;
}

// Get product ID and quantity
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($productId <= 0) {
    $response['message'] = 'Invalid product ID';
    echo json_encode($response);
    exit;
}

if ($quantity <= 0) {
    $quantity = 1;
}

// Get IP address
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Get customer ID (now guaranteed to be set)
$customerId = (int)$_SESSION['customer_id'];

// Add to cart - using customer ID as primary identifier
$result = add_to_cart_ctr($productId, $ipAddress, $customerId, $quantity);

if ($result) {
    // Get updated cart count
    $cartCount = get_cart_count_ctr($ipAddress, $customerId);
    
    $response = [
        'status' => 'success',
        'message' => 'Item added to cart successfully',
        'cart_count' => $cartCount
    ];
} else {
    $response['message'] = 'Failed to add item to cart';
}

echo json_encode($response);
exit;
