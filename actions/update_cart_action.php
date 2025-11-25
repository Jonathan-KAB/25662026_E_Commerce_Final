<?php
/**
 * Update Cart Action
 * Handles updating cart item quantities
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

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($productId <= 0) {
    $response['message'] = 'Invalid product ID';
    echo json_encode($response);
    exit;
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$customerId = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : null;

$result = update_cart_quantity_ctr($productId, $ipAddress, $customerId, $quantity);

if ($result) {
    $cartCount = get_cart_count_ctr($ipAddress, $customerId);
    $cartTotal = get_cart_total_ctr($ipAddress, $customerId);
    
    $response = [
        'status' => 'success',
        'message' => 'Cart updated successfully',
        'cart_count' => $cartCount,
        'cart_total' => number_format($cartTotal, 2)
    ];
} else {
    $response['message'] = 'Failed to update cart';
}

echo json_encode($response);
exit;

// Handle different actions
switch ($action) {
    case 'view_all':
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $offset = ($page - 1) * $limit;
        
        // Get all products
        $products = view_all_products_ctr($limit, $offset);
        $total = count_all_products_ctr();
        
        if ($products !== false) {
            echo json_encode([
                'status' => 'success',
                'data' => $products,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch products']);
        }
        break;
        
    case 'search':
        // Get search query and pagination
        $query = isset($_GET['query']) ? $_GET['query'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $offset = ($page - 1) * $limit;
        
        if (empty($query)) {
            echo json_encode(['status' => 'error', 'message' => 'Search query is required']);
            exit;
        }
        
        // Search products
        $products = search_products_ctr($query, $limit, $offset);
        $total = count_search_results_ctr($query);
        
        if ($products !== false) {
            echo json_encode([
                'status' => 'success',
                'data' => $products,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
                'query' => $query
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to search products']);
        }
        break;
        
    case 'filter_by_category':
        // Get category ID and pagination
        $cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $offset = ($page - 1) * $limit;
        
        if ($cat_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid category ID']);
            exit;
        }
        
        // Filter by category
        $products = filter_products_by_category_ctr($cat_id, $limit, $offset);
        $total = count_products_by_category_ctr($cat_id);
        
        if ($products !== false) {
            echo json_encode([
                'status' => 'success',
                'data' => $products,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
                'filter' => 'category',
                'filter_id' => $cat_id
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to filter products']);
        }
        break;
        
    case 'filter_by_brand':
        // Get brand ID and pagination
        $brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $offset = ($page - 1) * $limit;
        
        if ($brand_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid brand ID']);
            exit;
        }
        
        // Filter by brand
        $products = filter_products_by_brand_ctr($brand_id, $limit, $offset);
        $total = count_products_by_brand_ctr($brand_id);
        
        if ($products !== false) {
            echo json_encode([
                'status' => 'success',
                'data' => $products,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
                'filter' => 'brand',
                'filter_id' => $brand_id
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to filter products']);
        }
        break;
        
    case 'advanced_search':
        // Get filter parameters
        $filters = [
            'search' => isset($_GET['search']) ? $_GET['search'] : '',
            'category' => isset($_GET['category']) ? (int)$_GET['category'] : 0,
            'brand' => isset($_GET['brand']) ? (int)$_GET['brand'] : 0,
            'min_price' => isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0,
            'max_price' => isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0
        ];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
        $offset = ($page - 1) * $limit;
        
        // Advanced search
        $products = advanced_search_ctr($filters, $limit, $offset);
        $total = count_advanced_search_ctr($filters);
        
        if ($products !== false) {
            echo json_encode([
                'status' => 'success',
                'data' => $products,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
                'filters' => $filters
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to perform advanced search']);
        }
        break;
        
    case 'get_product':
        // Get product ID
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($product_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
            exit;
        }
        
        // Get single product
        $product = view_single_product_ctr($product_id);
        
        if ($product) {
            echo json_encode([
                'status' => 'success',
                'data' => $product
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        }
        break;
        
    case 'get_categories':
        // Get all categories for filter dropdown
        require_once '../controllers/product_controller.php';
        require_once '../settings/db_class.php';
        
        $db = new db_connection();
        if ($db->db_connect()) {
            $sql = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
            $categories = $db->db_fetch_all($sql);
            
            if ($categories) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $categories
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No categories found']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        }
        break;
        
    case 'get_brands':
        // Get all brands for filter dropdown
        require_once '../controllers/product_controller.php';
        require_once '../settings/db_class.php';
        
        $db = new db_connection();
        if ($db->db_connect()) {
            $sql = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC";
            $brands = $db->db_fetch_all($sql);
            
            if ($brands) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $brands
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No brands found']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
