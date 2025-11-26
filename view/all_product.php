<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../settings/db_class.php';

// Get cart count
$ipAddress = $_SERVER['REMOTE_ADDR'];
$customerId = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : null;
$cartCount = get_cart_count_ctr($ipAddress, $customerId);

// Pagination settings
$limit = 12; // Products per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : ''; // 'fabric' or 'service'

// Fetch products based on filters
if (!empty($search_query)) {
    $products = search_products_ctr($search_query, $limit, $offset);
    $total_products = count_search_results_ctr($search_query);
} elseif (!empty($type_filter) && in_array($type_filter, ['fabric', 'service'])) {
    // Filter by product type (fabric or service)
    $products = filter_products_by_type_ctr($type_filter, $limit, $offset);
    $total_products = count_products_by_type_ctr($type_filter);
} elseif ($category_filter > 0) {
    $products = filter_products_by_category_ctr($category_filter, $limit, $offset);
    $total_products = count_products_by_category_ctr($category_filter);
} elseif ($brand_filter > 0) {
    $products = filter_products_by_brand_ctr($brand_filter, $limit, $offset);
    $total_products = count_products_by_brand_ctr($brand_filter);
} else {
    $products = view_all_products_ctr($limit, $offset);
    $total_products = count_all_products_ctr();
}

$total_pages = ceil($total_products / $limit);

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
    <title>All Products - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-placeholder{height:220px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;color:#9ca3af;width:100%;}
        .product-placeholder i{font-size:36px}
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <!-- Success Message for Payment -->
    <?php if (isset($_GET['payment_success'])): ?>
    <div id="paymentSuccessAlert" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 16px 20px; text-align: center; position: sticky; top: 0; z-index: 999;">
        <strong><i class="fas fa-check-circle" style="margin-right:8px;color:inherit;"></i> Payment Successful!</strong> Your order has been confirmed. 
        <?php if (isset($_GET['order_id'])): ?>
            <a href="orders.php" style="color: #155724; text-decoration: underline; margin-left: 8px;">View Order Details</a>
        <?php endif; ?>
        <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: #155724; float: right; cursor: pointer; font-size: 20px; line-height: 1;">&times;</button>
    </div>
    <script>
        // Auto-hide success message after 10 seconds
        setTimeout(function() {
            const alert = document.getElementById('paymentSuccessAlert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }
        }, 10000);
    </script>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Discover Fabrics & Tailoring Services</h1>
            <p>Browse premium African fabrics and connect with skilled tailors</p>
        </div>
    </div>

    <div class="container">
        <!-- Product Type Tabs -->
        <div class="product-type-tabs" style="margin-bottom: 32px;">
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="?type=" class="type-tab <?= $type_filter === '' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i class="fas fa-th"></i> All Products & Services
                </a>
                <a href="?type=fabric" class="type-tab <?= $type_filter === 'fabric' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i class="fas fa-cut"></i> Fabrics & Materials
                </a>
                <a href="?type=service" class="type-tab <?= $type_filter === 'service' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i class="fas fa-user-tie"></i> Tailoring Services
                </a>
            </div>
        </div>

        <style>
            .type-tab {
                padding: 14px 28px;
                border-radius: 10px;
                border: 2px solid #e2e8f0;
                background: white;
                color: #64748b;
                font-weight: 600;
                transition: all 0.3s;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .type-tab:hover {
                border-color: #198754;
                color: #198754;
                background: #f0fdf4;
            }
            .type-tab.active {
                background: linear-gradient(135deg, #198754 0%, #157347 100%);
                color: white;
                border-color: #198754;
            }
            .type-tab i {
                font-size: 18px;
            }
        </style>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <form method="GET" action="" id="searchForm">
                <!-- Search Box -->
                <div class="search-box">
                    <input 
                        type="text" 
                        name="search" 
                        id="searchInput" 
                        placeholder="Search fabrics, services, or vendors..." 
                        value="<?= htmlspecialchars($search_query) ?>"
                    >
                    <button type="submit">Search</button>
                </div>

                <!-- Keeps the fabric/service filter when searching -->
                <?php if (!empty($type_filter)): ?>
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type_filter) ?>">
                <?php endif; ?>

                <!-- Filters -->
                <div class="filters">
                    <div class="filter-group">
                        <label for="categoryFilter">Filter by Category</label>
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
                        <label for="brandFilter">Filter by Vendor</label>
                        <select name="brand" id="brandFilter" onchange="this.form.submit()">
                            <option value="">All Vendors</option>
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
                        Clear Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Results Count -->
        <?php if ($total_products > 0): ?>
            <div class="results-count">
                Showing <?= (($page - 1) * $limit) + 1 ?> - <?= min($page * $limit, $total_products) ?> of <?= $total_products ?> 
                <?php 
                if ($type_filter === 'service') {
                    echo 'service providers';
                } elseif ($type_filter === 'fabric') {
                    echo 'fabrics & materials';
                } else {
                    echo 'results';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <?php if ($products && count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if (!empty($product['product_image'])): ?>
                            <?php 
                            // Handle both /uploads and uploads paths
                            $imagePath = $product['product_image'];
                            if (strpos($imagePath, '/') === 0) {
                                // Absolute path from root (school server)
                                echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['product_title']) . '">';
                            } else {
                                // Relative path (local XAMPP)
                                echo '<img src="../' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['product_title']) . '">';
                            }
                            ?>
                        <?php else: ?>
                            <div class="product-placeholder" role="img" aria-label="No image available">
                                <i class="fa fa-image" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <?php if ($type_filter === 'service'): ?>
                                <!-- Service Provider Display -->
                                <div class="product-category" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                                    <i class="fas fa-scissors"></i> SERVICE LISTING
                                </div>
                                <a href="single_product.php?id=<?= $product['product_id'] ?>" class="product-title" style="font-size: 1.25rem; font-weight: 700; color: #1f2937; display: block; margin-bottom: 10px; line-height: 1.4;">
                                    <?= htmlspecialchars($product['product_title']) ?>
                                </a>
                                <div class="product-brand" style="color: #059669; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; font-size: 0.95rem;">
                                    <i class="fas fa-store" style="color: #10b981;"></i> By: <?= htmlspecialchars($product['brand_name'] ?? 'Unknown') ?>
                                </div>
                                <div style="margin: 12px 0; padding: 12px; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-left: 3px solid #8b5cf6; border-radius: 8px; font-size: 0.9rem; color: #4c1d95;">
                                    <i class="fas fa-cut" style="color: #8b5cf6;"></i> <strong>Can Make:</strong> <?= htmlspecialchars($product['cat_name'] ?? 'Various Garments') ?>
                                </div>
                            <?php else: ?>
                                <!-- Product Display -->
                                <div class="product-category"><?= htmlspecialchars($product['cat_name'] ?? 'Uncategorized') ?></div>
                                <a href="single_product.php?id=<?= $product['product_id'] ?>" class="product-title">
                                    <?= htmlspecialchars($product['product_title']) ?>
                                </a>
                                <div class="product-brand">Vendor: <?= htmlspecialchars($product['brand_name'] ?? 'Unknown') ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($product['rating_average']) && $product['rating_average'] > 0): ?>
                                <div style="font-size: 14px; color: #666; margin: 8px 0;">
                                    <span style="color: #ffc107;">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= round($product['rating_average']) ? '★' : '☆';
                                        }
                                        ?>
                                    </span>
                                    <?= number_format($product['rating_average'], 1) ?>
                                    <?php if (isset($product['rating_count']) && $product['rating_count'] > 0): ?>
                                        (<?= $product['rating_count'] ?>)
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="price" style="<?= $type_filter === 'service' ? 'background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; padding: 12px 16px; border-radius: 8px; font-size: 1.1rem; font-weight: 700; margin: 12px 0; display: flex; align-items: center; gap: 8px;' : '' ?>">
                                <?php if ($type_filter === 'service'): ?>
                                    <i class="fas fa-money-bill-wave"></i> Starting at GH₵ <?= number_format($product['product_price'], 2) ?>
                                <?php else: ?>
                                    GH₵ <?= number_format($product['product_price'], 2) ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Stock Display -->
                            <?php if (isset($product['product_stock'])): ?>
                                <?php if ($product['product_stock'] >= 999): ?>
                                    <!-- Services or unlimited availability -->
                                    <div style="color: #8b5cf6; font-size: 0.875rem; font-weight: 600; margin: 12px 0; padding: 8px 12px; background: #f5f3ff; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-check-circle"></i> Available for Production
                                    </div>
                                <?php elseif ($product['product_stock'] > 0): ?>
                                    <?php if ($product['product_stock'] <= 10): ?>
                                        <div style="color: #dc3545; font-size: 0.875rem; font-weight: 600; margin: 8px 0;">
                                            <i class="fas fa-exclamation-triangle" style="margin-right:6px;color:inherit;"></i> Only <?= $product['product_stock'] ?> left!
                                        </div>
                                    <?php else: ?>
                                        <div style="color: #28a745; font-size: 0.875rem; margin: 8px 0;">
                                            <i class="fas fa-check-circle" style="margin-right:6px;color:inherit;"></i> In Stock (<?= $product['product_stock'] ?> available)
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="color: #dc3545; font-size: 0.875rem; font-weight: 600; margin: 8px 0;">
                                        <i class="fas fa-times-circle" style="margin-right:6px;color:inherit;"></i> Out of Stock
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Button - View Details for services (stock >= 999), Add to Cart for products -->
                            <?php if (isset($product['product_stock']) && $product['product_stock'] >= 999): ?>
                                <a href="single_product.php?id=<?= $product['product_id'] ?>" class="add-to-cart-btn" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px; border-radius: 8px; font-weight: 600; margin-top: 16px; box-shadow: 0 2px 4px rgba(139, 92, 246, 0.3); transition: all 0.2s;">
                                    <i class="fas fa-info-circle"></i> View Details
                                </a>
                            <?php else: ?>
                                <button class="add-to-cart-btn" onclick="addToCart(<?= $product['product_id'] ?>)" 
                                    <?= (isset($product['product_stock']) && $product['product_stock'] <= 0) ? 'disabled style="background: #ccc; cursor: not-allowed;"' : '' ?>>
                                    <?= (isset($product['product_stock']) && $product['product_stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    // Build query string for pagination
                    $query_params = [];
                    if (!empty($search_query)) $query_params['search'] = $search_query;
                    if (!empty($type_filter)) $query_params['type'] = $type_filter;
                    if ($category_filter > 0) $query_params['category'] = $category_filter;
                    if ($brand_filter > 0) $query_params['brand'] = $brand_filter;
                    
                    $base_url = 'all_product.php?' . http_build_query($query_params);
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
            <div class="no-products">
                <h3>
                    <?php 
                    if ($type_filter === 'service') {
                        echo 'No Tailors or Seamstresses Found';
                    } elseif ($type_filter === 'fabric') {
                        echo 'No Fabrics or Materials Found';
                    } else {
                        echo 'No Results Found';
                    }
                    ?>
                </h3>
                <p>
                    <?php 
                    if ($type_filter === 'service') {
                        echo 'No service providers match your criteria. Try browsing all services or adjusting your filters.';
                    } elseif ($type_filter === 'fabric') {
                        echo 'No fabrics or materials match your criteria. Try browsing all products or adjusting your filters.';
                    } else {
                        echo 'Try adjusting your search or filter criteria, or browse our full catalog.';
                    }
                    ?>
                </p>
                <a href="all_product.php" class="btn btn-primary" style="margin-top: 16px;">Browse All Products</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>

    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
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
                        } else {
                            // Create badge if it doesn't exist
                            $('a[href="cart.php"]').append('<span class="cart-badge" id="cart-count">' + response.cart_count + '</span>');
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

        // Optional: Add loading state to buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.textContent = 'Adding...';
                setTimeout(() => {
                    this.textContent = 'Add to Cart';
                }, 1000);
            });
        });
    </script>
</body>
</html>
