<?php
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../settings/core.php';
session_start();
header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn() || !isAdmin()) {
        $response['message'] = 'Not authorized';
        echo json_encode($response);
        exit;
    }
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    if ($product_id <= 0) {
        $response['message'] = 'Invalid product id';
        echo json_encode($response);
        exit;
    }
    $data = [];
    $data['product_cat'] = isset($_POST['product_cat']) ? (int)$_POST['product_cat'] : 0;
    $data['product_brand'] = isset($_POST['product_brand']) ? (int)$_POST['product_brand'] : 0;
    $data['product_title'] = isset($_POST['product_title']) ? trim($_POST['product_title']) : '';
    $data['product_price'] = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0;
    $data['product_desc'] = isset($_POST['product_desc']) ? trim($_POST['product_desc']) : '';
    $data['product_keywords'] = isset($_POST['product_keywords']) ? trim($_POST['product_keywords']) : '';
    $data['product_stock'] = isset($_POST['product_stock']) ? (int)$_POST['product_stock'] : 0;
    $res = update_product_ctr($product_id, $data);
    if ($res) {
        $response = ['status' => 'success'];
    } else {
        $response['message'] = 'Failed to update product';
        // attempt to capture DB error for debugging
        if (class_exists('Product')) {
            $p = new Product();
            $err = $p->getLastError();
            $lastq = $p->getLastQuery();
            $response['message'] .= '. DB error: ' . $err;
            $log = "[".date('c')."] UPDATE_PRODUCT FAILED: DB error: $err\nPRODUCT_ID: $product_id\nPAYLOAD: ".json_encode($data)."\nQUERY: $lastq\n\n";
            @file_put_contents(__DIR__.'/../logs/update_product_errors.log', $log, FILE_APPEND);
        }
    }

    // Always append a debug line so we can see attempted updates even when they succeed
    if (class_exists('Product')) {
        if (!isset($p)) $p = new Product();
        $err = $p->getLastError();
        $lastq = $p->getLastQuery();
        $debug = "[".date('c')."] UPDATE_PRODUCT ATTEMPT: RESULT:" . ($res ? '1' : '0') . " PRODUCT_ID:$product_id PAYLOAD:" . json_encode($data) . " ERR:" . $err . " QUERY:" . $lastq . "\n";
        @file_put_contents(__DIR__.'/../logs/update_product_errors.log', $debug, FILE_APPEND);
    }
}
echo json_encode($response);
// single JSON response