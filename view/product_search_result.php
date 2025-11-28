<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../settings/db_class.php';

// Get search and filter parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

// Pagination settings
$limit = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build filters array for advanced search
$filters = [];
if (!empty($search_query)) {
    $filters['query'] = $search_query;
}
if ($category_filter > 0) {
    $filters['category'] = $category_filter;
}
if ($brand_filter > 0) {
    $filters['brand'] = $brand_filter;
}

// Fetch search results
if (!empty($filters)) {
    $products = advanced_search_ctr($filters, $limit, $offset);
    $total_products = count_advanced_search_ctr($filters);
} else {
    // If no filters, redirect to all products page
    header('Location: all_product.php');
    exit;
}

$total_pages = $total_products > 0 ? ceil($total_products / $limit) : 0;

// Get all categories and brands for filters
$db = new db_connection();
$db->db_connect();
$categories = $db->db_fetch_all("SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC");
$brands = $db->db_fetch_all("SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background-color: #fff;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #007bff;
        }

        /* Search Header */
        .search-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .search-query-display {
            font-size: 16px;
            color: #666;
        }

        .search-query-display strong {
            color: #007bff;
        }

        /* Active Filters */
        .active-filters {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-label {
            font-weight: 600;
            color: #333;
        }

        .filter-tag {
            padding: 5px 12px;
            background-color: #007bff;
            color: white;
            border-radius: 15px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .filter-tag .remove-filter {
            cursor: pointer;
            font-weight: bold;
        }

        /* Refine Search Section */
        .refine-search-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .refine-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input[type="text"] {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .search-box button {
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .search-box button:hover {
            background-color: #0056b3;
        }

        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }

        .clear-filters {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            align-self: flex-end;
        }

        .clear-filters:hover {
            background-color: #5a6268;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            /* height and object-fit are controlled by css/app.css variables */
            background-color: #f0f0f0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 12px;
            color: #007bff;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .product-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            text-decoration: none;
            display: block;
        }

        .product-title:hover {
            color: #007bff;
        }

        .product-brand {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 15px;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s;
            margin-top: auto;
        }

        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .no-results h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }

        .no-results p {
            color: #999;
            margin-bottom: 20px;
        }

        .no-results a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .no-results a:hover {
            background-color: #0056b3;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination a,
        .pagination span {
            padding: 10px 15px;
            background-color: #fff;
            color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination .current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Results Count */
        .results-count {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="../index.php" class="logo">E-Commerce Store</a>
            <nav class="nav-links">
                <a href="../index.php">Home</a>
                <a href="all_product.php">All Products</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../login/logout.php">Logout</a>
                <?php else: ?>
                    <a href="../login/login.php">Login</a>
                    <a href="../login/register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <div class="container">
        <!-- Search Header -->
        <div class="search-header">
            <h1 class="search-title">Search Results</h1>
            <?php if (!empty($search_query)): ?>
                <p class="search-query-display">
                    Results for: <strong>"<?= htmlspecialchars($search_query) ?>"</strong>
                </p>
            <?php endif; ?>

            <!-- Active Filters Display -->
            <?php if ($category_filter > 0 || $brand_filter > 0): ?>
                <div class="active-filters">
                    <span class="filter-label">Active Filters:</span>
                    <?php if ($category_filter > 0): ?>
                        <?php
                        $cat_name = '';
                        foreach ($categories as $cat) {
                            if ($cat['cat_id'] == $category_filter) {
                                $cat_name = $cat['cat_name'];
                                break;
                            }
                        }
                        $remove_cat_params = $_GET;
                        unset($remove_cat_params['category']);
                        ?>
                        <span class="filter-tag">
                            Category: <?= htmlspecialchars($cat_name) ?>
                            <a href="?<?= http_build_query($remove_cat_params) ?>" 
                               class="remove-filter" title="Remove filter">✕</a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($brand_filter > 0): ?>
                        <?php
                        $brand_name = '';
                        foreach ($brands as $brand) {
                            if ($brand['brand_id'] == $brand_filter) {
                                $brand_name = $brand['brand_name'];
                                break;
                            }
                        }
                        $remove_brand_params = $_GET;
                        unset($remove_brand_params['brand']);
                        ?>
                        <span class="filter-tag">
                            Brand: <?= htmlspecialchars($brand_name) ?>
                            <a href="?<?= http_build_query($remove_brand_params) ?>" 
                               class="remove-filter" title="Remove filter">✕</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Refine Search Section -->
        <div class="refine-search-section">
            <h2 class="refine-title">Refine Your Search</h2>
            <form method="GET" action="">
                <!-- Search Box -->
                <div class="search-box">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search products..." 
                        value="<?= htmlspecialchars($search_query) ?>"
                    >
                    <button type="submit">Search</button>
                </div>

                <!-- Filters -->
                <div class="filters">
                    <div class="filter-group">
                        <label for="categoryFilter">Category</label>
                        <select name="category" id="categoryFilter" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['cat_id'] ?>" <?= $category_filter == $cat['cat_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['cat_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="brandFilter">Brand</label>
                        <select name="brand" id="brandFilter" onchange="this.form.submit()">
                            <option value="">All Brands</option>
                            <?php if ($brands): ?>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?= $brand['brand_id'] ?>" <?= $brand_filter == $brand['brand_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($brand['brand_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button type="button" class="clear-filters" onclick="window.location.href='all_product.php'">
                        Clear All
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <?php if ($total_products > 0): ?>
            <div class="results-count">
                Showing <?= (($page - 1) * $limit) + 1 ?> - <?= min($page * $limit, $total_products) ?> of <?= $total_products ?> results
            </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <?php if ($products && count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($product['product_image']) ?>" 
                                     alt="<?= htmlspecialchars($product['product_title']) ?>">
                            <?php else: ?>
                                <div role="img" aria-label="No image available" class="product-image-placeholder">
                                    <i class="fas fa-image" style="font-size:36px;color:#9ca3af;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?= htmlspecialchars($product['cat_name'] ?? 'Uncategorized') ?></div>
                            <a href="single_product.php?id=<?= $product['product_id'] ?>" class="product-title">
                                <?= htmlspecialchars($product['product_title']) ?>
                            </a>
                                <div class="product-brand">Brand: <?= htmlspecialchars($product['brand_name'] ?? 'Unknown') ?></div>
                                <div class="product-seller--compact">
                                    <?php if (!empty($product['seller_image'])): ?>
                                        <?php $simg = htmlspecialchars($product['seller_image']); $simgsrc = (strpos($simg, '/uploads') === 0) ? $simg : ('../' . $simg); ?>
                                        <img class="seller-logo" src="<?= $simgsrc ?>" alt="<?= htmlspecialchars($product['seller_name'] ?? $product['brand_name'] ?? 'Seller') ?>">
                                    <?php else: ?>
                                        <div class="seller-avatar"><?= strtoupper(substr(($product['seller_name'] ?? $product['brand_name'] ?? 'U'), 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <div class="seller-info">
                                        <div class="seller-label">Supplied by</div>
                                        <div class="seller-details">
                                            <?php if (!empty($product['seller_id'])): ?>
                                                <a href="seller_profile.php?id=<?= (int)$product['seller_id'] ?>" class="seller-name"><?= htmlspecialchars($product['seller_name'] ?? $product['brand_name'] ?? 'Unknown') ?></a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($product['brand_name'] ?? 'Unknown') ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <div class="product-price">GH₵ <?= number_format($product['product_price'], 2) ?></div>
                            <button class="add-to-cart-btn" onclick="addToCart(<?= $product['product_id'] ?>)">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $base_url = 'product_search_result.php?' . http_build_query($query_params);
                    $separator = empty($query_params) ? '' : '&';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?= $base_url . $separator ?>page=<?= $page - 1 ?>">« Previous</a>
                    <?php else: ?>
                        <span class="disabled">« Previous</span>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= $base_url . $separator ?>page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?= $base_url . $separator ?>page=<?= $page + 1 ?>">Next »</a>
                    <?php else: ?>
                        <span class="disabled">Next »</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <h3>No Results Found</h3>
                <p>We couldn't find any products matching your search criteria.</p>
                <p>Try adjusting your search terms or filters.</p>
                <a href="all_product.php">View All Products</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add to cart functionality
        function addToCart(productId) {
            $.ajax({
                url: '../actions/add_to_cart_action.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Item added to cart successfully!');
                        // Update cart count if you have a cart badge
                        if ($('#cart-count').length) {
                            $('#cart-count').text(response.cart_count).show();
                        }
                    } else {
                        // Check if redirect is needed (not logged in)
                        if (response.redirect) {
                            if (confirm(response.message + '. Redirect to login page?')) {
                                window.location.href = response.redirect;
                            }
                        } else {
                            alert(response.message || 'Failed to add item to cart');
                        }
                    }
                },
                error: function() {
                    alert('Error adding item to cart');
                }
            });
        }
    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>
</body>
</html>
