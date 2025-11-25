<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../classes/brand_class.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
    $brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
    $user_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;

    if ($brand_id <= 0 || $brand_name === '' || $user_id <= 0) {
        $response['message'] = 'Missing parameters or not logged in';
        echo json_encode($response);
        exit;
    }

    $brand = new Brand();
    $ok = $brand->updateBrand($brand_id, $brand_name, $user_id);
    if ($ok) {
        $response = ['status' => 'success'];
    } else {
        $response['message'] = 'Failed to update brand (not found or not owned by user)';
    }
}

echo json_encode($response);