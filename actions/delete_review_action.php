<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

if (!isLoggedIn()) {
    $response['message'] = 'You must be logged in';
    echo json_encode($response);
    exit;
}

$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;

if (!$review_id) {
    $response['message'] = 'Invalid review ID';
    echo json_encode($response);
    exit;
}

$db = new db_connection();
if (!$db->db_connect()) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Verify the review belongs to this user
$check_sql = "SELECT product_id FROM product_reviews WHERE review_id = $review_id AND customer_id = $customer_id";
$review = $db->db_fetch_one($check_sql);

if (!$review) {
    $response['message'] = 'Review not found or you do not have permission to delete it';
    echo json_encode($response);
    exit;
}

$product_id = $review['product_id'];

// Delete the review
$delete_sql = "DELETE FROM product_reviews WHERE review_id = $review_id";
$result = $db->db_query($delete_sql);

if ($result) {
    // Update product rating
    $update_sql = "UPDATE products p SET 
        rating_average = COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = $product_id), 0),
        rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = $product_id)
        WHERE product_id = $product_id";
    $db->db_query($update_sql);
    
    $response = [
        'status' => 'success',
        'message' => 'Review deleted successfully'
    ];
} else {
    $response['message'] = 'Failed to delete review';
}

echo json_encode($response);
