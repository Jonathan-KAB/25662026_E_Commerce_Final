<?php
// Get products for a specific seller
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../settings/db_class.php';
header('Content-Type: application/json');

// Check if seller_id is provided
if (!isset($_GET['seller_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Seller ID is required']);
    exit;
}

$seller_id = (int)$_GET['seller_id'];

if ($seller_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid seller ID']);
    exit;
}

try {
    $db = new db_connection();
    $db->db_connect();
    
    // Get products for this seller with all relevant details
    $sql = "SELECT p.*, 
            c.cat_name, 
            b.brand_name,
            COALESCE(p.rating_average, 0) as avg_rating,
            COALESCE(p.rating_count, 0) as review_count,
            COALESCE(p.product_stock, 0) as in_stock
            FROM products p
            LEFT JOIN categories c ON p.product_cat = c.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            WHERE p.seller_id = $seller_id
            ORDER BY p.product_id DESC";
    
    $products = $db->db_fetch_all($sql);
    
    if ($products === false) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch products']);
        exit;
    }
    
    echo json_encode([
        'status' => 'success', 
        'data' => $products,
        'count' => count($products)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}