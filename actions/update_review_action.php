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
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$review_title = isset($_POST['review_title']) ? trim($_POST['review_title']) : '';

if (!$review_id || !$rating || $rating < 1 || $rating > 5) {
    $response['message'] = 'Invalid review ID or rating';
    echo json_encode($response);
    exit;
}

if (strlen($review_text) < 10) {
    $response['message'] = 'Review must be at least 10 characters long';
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
    $response['message'] = 'Review not found or you do not have permission to edit it';
    echo json_encode($response);
    exit;
}

$product_id = $review['product_id'];

// Escape strings
$review_text_escaped = $db->db_conn()->real_escape_string($review_text);
$review_title_escaped = $db->db_conn()->real_escape_string($review_title);

// Check if review_title column exists
$columns = $db->db_fetch_all("SHOW COLUMNS FROM product_reviews");
$has_title = false;
$has_updated_at = false;
foreach ($columns as $col) {
    if ($col['Field'] == 'review_title') $has_title = true;
    if ($col['Field'] == 'updated_at') $has_updated_at = true;
}

// Build UPDATE query
if ($has_title && $has_updated_at) {
    $update_sql = "UPDATE product_reviews SET 
        rating = $rating,
        review_title = '$review_title_escaped',
        review_text = '$review_text_escaped',
        updated_at = NOW()
        WHERE review_id = $review_id";
} elseif ($has_title) {
    $update_sql = "UPDATE product_reviews SET 
        rating = $rating,
        review_title = '$review_title_escaped',
        review_text = '$review_text_escaped'
        WHERE review_id = $review_id";
} else {
    $update_sql = "UPDATE product_reviews SET 
        rating = $rating,
        review_text = '$review_text_escaped'
        WHERE review_id = $review_id";
}

$result = $db->db_query($update_sql);

if ($result) {
    // Update product rating
    $update_rating_sql = "UPDATE products p SET 
        rating_average = (SELECT AVG(rating) FROM product_reviews WHERE product_id = $product_id),
        rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = $product_id)
        WHERE product_id = $product_id";
    $db->db_query($update_rating_sql);
    
    $response = [
        'status' => 'success',
        'message' => 'Review updated successfully'
    ];
} else {
    $response['message'] = 'Failed to update review';
}

echo json_encode($response);
