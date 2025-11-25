<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../classes/brand_class.php';

header('Content-Type: application/json');

$user_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;
if ($user_id <= 0) {
	echo json_encode([]);
	exit;
}

$brand = new Brand();

// If admin, fetch all brands, otherwise fetch only user's brands
if (isAdmin()) {
	$items = $brand->db_fetch_all("SELECT b.brand_id, b.brand_name, b.brand_cat, c.cat_name FROM brands b LEFT JOIN categories c ON b.brand_cat = c.cat_id ORDER BY c.cat_name, b.brand_name");
} else {
	$items = $brand->fetchBrandsByUser($user_id);
}

if ($items === false) {
	$err = (isset($brand->db) && $brand->db) ? mysqli_error($brand->db) : '';
	echo json_encode(['status' => 'error', 'message' => 'DB fetch failed', 'debug' => $err]);
} else {
	echo json_encode($items);
}