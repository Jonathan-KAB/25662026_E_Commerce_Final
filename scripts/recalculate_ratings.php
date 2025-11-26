<?php
// Recalculate product rating aggregates from product_reviews
// Usage (CLI): php recalculate_ratings.php [seller_id]
// Usage (web): /scripts/recalculate_ratings.php?seller_id=123

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';

// Simple auth for web usage: restrict to admin sessions if available
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!function_exists('isAdmin') || !isAdmin()) {
        echo "Access denied. Admins only.";
        exit;
    }
}

$seller_id = null;
if (php_sapi_name() === 'cli') {
    $argv = $_SERVER['argv'];
    if (isset($argv[1]) && is_numeric($argv[1])) {
        $seller_id = (int)$argv[1];
    }
} else {
    if (isset($_GET['seller_id']) && is_numeric($_GET['seller_id'])) {
        $seller_id = (int)$_GET['seller_id'];
    }
}

$db = new db_connection();
if (!$db->db_connect()) {
    echo "Failed to connect to database\n";
    exit(1);
}

// Detect if product_reviews has a status column
$columns = $db->db_fetch_all("SHOW COLUMNS FROM product_reviews");
$has_status = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'status') { $has_status = true; break; }
}

$status_condition = $has_status ? "AND status = 'approved'" : "";

// Build product list
$prod_sql = "SELECT product_id, product_title, seller_id FROM products";
if ($seller_id) {
    $prod_sql .= " WHERE seller_id = $seller_id";
}

$products = $db->db_fetch_all($prod_sql);
if (!$products) {
    echo "No products found\n";
    exit(0);
}

$summary = [];
foreach ($products as $p) {
    $pid = (int)$p['product_id'];
    $agg_sql = "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS cnt FROM product_reviews WHERE product_id = $pid AND rating >= 1 $status_condition";
    $agg = $db->db_fetch_one($agg_sql);
    $avg = isset($agg['avg_rating']) ? (float)$agg['avg_rating'] : 0.0;
    $cnt = isset($agg['cnt']) ? (int)$agg['cnt'] : 0;

    // Update product row
    $update_sql = "UPDATE products SET rating_average = $avg, rating_count = $cnt WHERE product_id = $pid";
    $db->db_query($update_sql);

    $summary[] = [
        'product_id' => $pid,
        'title' => $p['product_title'] ?? '',
        'seller_id' => $p['seller_id'],
        'avg' => $avg,
        'count' => $cnt
    ];
}

// Output results
if (php_sapi_name() === 'cli') {
    foreach ($summary as $s) {
        echo "Product {$s['product_id']} ({$s['title']}) - avg={$s['avg']} count={$s['count']}\n";
    }
    echo "Done. Updated " . count($summary) . " products.\n";
} else {
    echo "<h2>Recalculated ratings for " . count($summary) . " products</h2>\n";
    echo "<table border=1 cellpadding=6 cellspacing=0>\n";
    echo "<tr><th>Product ID</th><th>Title</th><th>Seller ID</th><th>Avg</th><th>Count</th></tr>\n";
    foreach ($summary as $s) {
        echo "<tr><td>{$s['product_id']}</td><td>" . htmlspecialchars($s['title']) . "</td><td>{$s['seller_id']}</td><td>{$s['avg']}</td><td>{$s['count']}</td></tr>\n";
    }
    echo "</table>\n";
}

return 0;

?>
