<?php
// Enable error display for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get seller ID from URL or show error
$seller_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$seller_id) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

$ipAddress = $_SERVER['REMOTE_ADDR'];
$customerId = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : null;
$cartCount = get_cart_count_ctr($ipAddress, $customerId);

$db = new db_connection();
$db->db_connect();

// Get seller profile info
$seller_sql = "SELECT c.customer_id, c.customer_name, c.customer_email, c.customer_contact, 
               c.customer_city, c.customer_country, c.created_at, c.service_type, c.user_role,
               c.customer_image
               FROM customer c
               WHERE c.customer_id = $seller_id AND (c.user_role = 3 OR c.user_role = 4)";

$seller = $db->db_fetch_one($seller_sql);

if (!$seller) {
    header("Location: ../index.php");
    exit();
}

// Add default values for seller_profiles fields that don't exist
$seller['store_name'] = null;
$seller['store_description'] = null;
$seller['store_logo'] = null;
$seller['store_banner'] = null;
$seller['contact_phone'] = null;
$seller['contact_email'] = null;
$seller['business_address'] = null;
$seller['social_facebook'] = null;
$seller['social_instagram'] = null;
$seller['social_twitter'] = null;
$seller['rating_average'] = 0;
$seller['total_sales'] = 0;
$seller['verified'] = false;
$seller['seller_since'] = $seller['created_at'];

// Get seller's products
$products_sql = "SELECT p.*, c.cat_name, b.brand_name,
                 COALESCE(p.rating_average, 0) as avg_rating,
                 COALESCE(p.rating_count, 0) as review_count
                 FROM products p
                 LEFT JOIN categories c ON p.product_cat = c.cat_id
                 LEFT JOIN brands b ON p.product_brand = b.brand_id
                 WHERE p.seller_id = $seller_id
                 ORDER BY p.product_id DESC";

$products = $db->db_fetch_all($products_sql);
if (!$products) {
    $products = [];
}

// Get total reviews for this seller (if product_reviews table exists)
$total_reviews = 0;
$check_reviews_table = $db->db_fetch_one("SHOW TABLES LIKE 'product_reviews'");
if ($check_reviews_table) {
    $reviews_sql = "SELECT COUNT(*) as total FROM product_reviews pr
                    JOIN products p ON pr.product_id = p.product_id
                    WHERE p.seller_id = $seller_id";
    $review_stats = $db->db_fetch_one($reviews_sql);
    $total_reviews = $review_stats['total'] ?? 0;
}

// Calculate seller's overall rating (average across all product reviews)
$seller['rating_average'] = 0;
if ($check_reviews_table) {
    // Check if product_reviews has a status column and only consider approved reviews
    $columns = $db->db_fetch_all("SHOW COLUMNS FROM product_reviews");
    $has_status = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'status') { $has_status = true; break; }
    }
    $status_condition = $has_status ? "AND pr.status = 'approved'" : "";

    // Only use reviews with a valid rating (>=1) to compute average
    $seller_rating_sql = "SELECT COALESCE(AVG(pr.rating), 0) AS avg_rating, COUNT(*) AS total_reviews
                          FROM product_reviews pr
                          JOIN products p ON pr.product_id = p.product_id
                          WHERE p.seller_id = $seller_id AND pr.rating >= 1 $status_condition";
    $seller_rating_stats = $db->db_fetch_one($seller_rating_sql);
    if ($seller_rating_stats) {
        $seller['rating_average'] = (float)$seller_rating_stats['avg_rating'];
        $total_reviews = (int)$seller_rating_stats['total_reviews'];
    }
}

// Fallback: if there are no individual reviews found, aggregate from product-level stored ratings
if (empty($total_reviews)) {
    $product_agg_sql = "SELECT COALESCE(SUM(p.rating_average * p.rating_count) / NULLIF(SUM(p.rating_count),0), 0) AS avg_rating,
                                COALESCE(SUM(p.rating_count), 0) AS total_reviews
                         FROM products p
                         WHERE p.seller_id = $seller_id AND COALESCE(p.rating_count, 0) > 0";
    $product_agg = $db->db_fetch_one($product_agg_sql);
    if ($product_agg) {
        $seller['rating_average'] = (float)$product_agg['avg_rating'];
        $total_reviews = (int)$product_agg['total_reviews'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seller['store_name'] ?? $seller['customer_name']) ?> - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .seller-banner {
            width: 100%;
            height: 160px; /* reduce banner height for less vertical weight */
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .seller-info-card {
            max-width: 1100px;
            margin: -48px auto 20px; /* reduce overlap so header feels lighter */
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 14px; /* slightly smaller padding */
            position: relative;
        }
        .seller-info-card h1 { margin: 0; font-size: 1.6rem; }
        .seller-logo {
            width: 96px; /* smaller logo size */
            height: 96px;
            border-radius: 50%;
            border: 4px solid white; /* keep border but slightly smaller */
            box-shadow: var(--shadow-md);
            object-fit: cover;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--gray-400);
        }
        .seller-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px; /* less vertical space between stat cards */
            margin-top: 12px;
        }
        .stat-card {
            text-align: center;
            padding: 12px; /* smaller paddings */
            background: var(--gray-50);
            border-radius: var(--radius-md);
        }
        .stat-value {
            font-size: 1.5rem; /* smaller stat numbers */
            font-weight: 700;
            color: var(--primary);
        }
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 4px;
        }
        .verified-badge {
            background: var(--success);
            color: white;
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        /* Mobile responsive adjustments for seller profile */
        @media (max-width: 768px) {
            .seller-banner { height: 140px; }
            .seller-info-card { margin: -40px auto 16px; padding: 14px; }
            /* override inline grid: stack logo above details */
            .seller-info-card > div { display: grid !important; grid-template-columns: 1fr !important; gap: 12px !important; align-items: center !important; }
            .seller-logo { width: 72px; height: 72px; font-size: 1.5rem; border-width: 4px; }
            .seller-info-card h1 { font-size: 1.25rem; text-align: center; }
            .seller-info-card > div > div:last-child { text-align: center; }
            .seller-stats { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; }
            .stat-card { padding: 10px; }
            .products-grid { grid-template-columns: 1fr !important; gap: 16px !important; }
            .product-card { display: flex; flex-direction: column; }
            /* product images at this breakpoint rely on global app.css variables */
            .product-card .card-body { padding: 12px; }
            .product-card .btn { width: 100%; }
            .service-type-badge, .verified-badge { margin-left: 0; margin-top: 8px; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <!-- Seller Banner -->
    <div class="seller-banner" <?php if ($seller['store_banner']): ?>style="background-image: url('../uploads/<?= htmlspecialchars($seller['store_banner']) ?>');"<?php endif; ?>>
    </div>

    <!-- Seller Info Card -->
    <div class="container">
        <div class="seller-info-card">
            <div style="display: grid; grid-template-columns: 96px 1fr; gap: 16px; align-items: center;">
                <!-- Seller Logo -->
                <div class="seller-logo">
                    <?php if ($seller['customer_image']): ?>
                        <?php
                        // Handle image path for Ashesi server
                        $imagePath = $seller['customer_image'];
                        if (strpos($imagePath, '/uploads') === 0) {
                            $imageSrc = htmlspecialchars($imagePath);
                        } elseif (strpos($imagePath, 'uploads/') === 0) {
                            $imageSrc = '../' . htmlspecialchars($imagePath);
                        } else {
                            $imageSrc = '../' . htmlspecialchars($imagePath);
                        }
                        ?>
                        <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($seller['customer_name']) ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    <?php elseif ($seller['store_logo']): ?>
                        <img src="../uploads/<?= htmlspecialchars($seller['store_logo']) ?>" alt="Store Logo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <?= strtoupper(substr($seller['customer_name'], 0, 1)) ?>
                    <?php endif; ?>
                </div>

                <!-- Seller Details -->
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <h1 style="margin: 0;"><?= htmlspecialchars($seller['store_name'] ?? $seller['customer_name']) ?></h1>
                        <?php if ($seller['verified']): ?>
                            <span class="verified-badge"><i class="fas fa-check-circle" aria-hidden="true" style="margin-right:6px;"></i> Verified Seller</span>
                        <?php endif; ?>
                        <?php 
                        // Check if this is a service provider (role 4)
                        $is_service_provider = ($seller['user_role'] == 4);
                        if ($is_service_provider && !empty($seller['service_type']) && $seller['service_type'] !== 'none'): 
                        ?>
                            <span class="service-type-badge" style="background: linear-gradient(135deg, #9b87f5 0%, #7c3aed 100%); color: white; padding: 4px 12px; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">
                                <?php 
                                $service_icons = [
                                    'tailor' => '<i class="fas fa-user-tie"></i>',
                                    'seamstress' => '<i class="fas fa-cut"></i>',
                                    'general' => '<i class="fas fa-star"></i>'
                                ];
                                $icon = $service_icons[$seller['service_type']] ?? '<i class="fas fa-briefcase"></i>';
                                echo $icon . ' ' . htmlspecialchars(ucfirst($seller['service_type']));
                                ?>
                            </span>
                        <?php elseif ($seller['user_role'] == 3): ?>
                            <span class="service-type-badge" style="background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%); color: white; padding: 4px 12px; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 600;">
                                <i class="fas fa-store"></i> Fabric Seller
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($seller['store_description']): ?>
                        <p style="color: var(--gray-700); margin-bottom: 8px; line-height: 1.45; font-size: 0.95rem;">
                            <?= nl2br(htmlspecialchars($seller['store_description'])) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Contact Info -->
                    <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
                        <?php if ($seller['contact_phone'] ?? $seller['customer_contact']): ?>
                            <span style="color: var(--gray-600); font-size: 0.9rem; display: inline-flex; gap:8px; align-items:center;">
                                <i class="fas fa-phone" style="color:var(--primary);"></i>
                                <?= htmlspecialchars($seller['contact_phone'] ?? $seller['customer_contact']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($seller['contact_email'] ?? $seller['customer_email']): ?>
                            <span style="color: var(--gray-600); font-size: 0.9rem; display: inline-flex; gap:8px; align-items:center;">
                                <i class="fas fa-envelope" style="color:var(--primary);"></i>
                                <?= htmlspecialchars($seller['contact_email'] ?? $seller['customer_email']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($seller['business_address'] ?? ($seller['customer_city'] . ', ' . $seller['customer_country'])): ?>
                            <span style="color: var(--gray-600); font-size: 0.9rem; display: inline-flex; gap:8px; align-items:center;">
                                <i class="fas fa-map-marker-alt" style="color:var(--primary);"></i>
                                <?= htmlspecialchars($seller['business_address'] ?? ($seller['customer_city'] . ', ' . $seller['customer_country'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Social Links -->
                    <?php if ($seller['social_facebook'] || $seller['social_instagram'] || $seller['social_twitter']): ?>
                        <div style="display: flex; gap: 12px;">
                            <?php if ($seller['social_facebook']): ?>
                                <a href="<?= htmlspecialchars($seller['social_facebook']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Facebook</a>
                            <?php endif; ?>
                            <?php if ($seller['social_instagram']): ?>
                                <a href="<?= htmlspecialchars($seller['social_instagram']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Instagram</a>
                            <?php endif; ?>
                            <?php if ($seller['social_twitter']): ?>
                                <a href="<?= htmlspecialchars($seller['social_twitter']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Twitter</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seller Stats -->
            <div class="seller-stats">
                <div class="stat-card">
                    <div class="stat-value"><?= count($products) ?></div>
                    <div class="stat-label"><?= $is_service_provider ? 'Service Listings' : 'Products' ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($seller['rating_average'] ?? 0, 1) ?> <i class="fas fa-star" style="color:var(--primary);"></i></div>
                    <div class="stat-label">Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $total_reviews ?></div>
                    <div class="stat-label">Reviews</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $seller['total_sales'] ?? 0 ?></div>
                    <div class="stat-label"><?= $is_service_provider ? 'Bookings' : 'Sales' ?></div>
                </div>
            </div>
        </div>

        <!-- Seller's Products -->
        <div style="margin-bottom: 60px;">
            <h2 style="margin-bottom: 24px;"><?= $is_service_provider ? 'Services from this Provider' : 'Products from this Store' ?></h2>
            
                    <?php if (empty($products)): ?>
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 64px; margin-bottom: 16px;"><i class="fas fa-box" style="color:var(--gray-400);"></i></div>
                        <h3 style="color: var(--gray-600); margin-bottom: 12px;">No Products Yet</h3>
                        <p style="color: var(--gray-500);">This seller hasn't listed any products yet.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="products-grid seller-profile">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php if ($product['product_image']): ?>
                                <?php
                                // Handle image path for Ashesi server
                                $prodImagePath = $product['product_image'];
                                if (strpos($prodImagePath, '/uploads') === 0) {
                                    $prodImageSrc = htmlspecialchars($prodImagePath);
                                } elseif (strpos($prodImagePath, 'uploads/') === 0) {
                                    $prodImageSrc = '../' . htmlspecialchars($prodImagePath);
                                } else {
                                    $prodImageSrc = '../' . htmlspecialchars($prodImagePath);
                                }
                                ?>
                                <img src="<?= $prodImageSrc ?>" alt="<?= htmlspecialchars($product['product_title']) ?>">
                            <?php else: ?>
                                <div class="product-image-placeholder"><i class="fas fa-box" style="font-size:28px;color:var(--gray-400);"></i></div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="product-category" style="font-size: 0.75rem; color: var(--primary); text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">
                                    <?= htmlspecialchars($product['cat_name']) ?>
                                </div>
                                
                                <a href="single_product.php?id=<?= $product['product_id'] ?>" class="product-title">
                                    <?= htmlspecialchars($product['product_title']) ?>
                                </a>
                                
                                <div class="product-meta">
                                    <?= htmlspecialchars($product['brand_name']) ?>
                                </div>
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

                                <?php if ($product['review_count'] > 0): ?>
                                    <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 8px;">
                                        <i class="fas fa-star" style="color:var(--primary);"></i> <?= number_format($product['avg_rating'], 1) ?> (<?= $product['review_count'] ?> reviews)
                                    </div>
                                <?php endif; ?>
                                
                                <div class="price">GH₵ <?= number_format($product['product_price'], 2) ?></div>
                                
                                <!-- Stock Display - hide for services (stock 999) -->
                                <?php if (isset($product['product_stock']) && $product['product_stock'] < 999): ?>
                                    <?php if ($product['product_stock'] > 0 && $product['product_stock'] <= 10): ?>
                                        <div class="stock-bubble warn"><i class="fas fa-exclamation-triangle"></i> Only <?= $product['product_stock'] ?> left!</div>
                                    <?php elseif ($product['product_stock'] > 10): ?>
                                        <div class="stock-bubble in-stock"><i class="fas fa-check-circle"></i> In Stock (<?= $product['product_stock'] ?> available)</div>
                                    <?php else: ?>
                                        <div class="stock-bubble out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- Button - View Details for services, Add to Cart for products -->
                                <?php if (isset($product['product_stock']) && $product['product_stock'] >= 999): ?>
                                    <a href="single_product.php?id=<?= $product['product_id'] ?>" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 6px; text-decoration: none; display: block; text-align: center;">
                                        View Details
                                    </a>
                                <?php else: ?>
                                    <button onclick="addToCart(<?= $product['product_id'] ?>)" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 6px;"
                                        <?= (isset($product['product_stock']) && $product['product_stock'] <= 0) ? 'disabled style="background: #ccc; cursor: not-allowed; width: 100%; margin-top: 8px;"' : '' ?>>
                                        <?= (isset($product['product_stock']) && $product['product_stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer Spacing -->
    <div style="height: 40px;"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/cart.js"></script>
</body>
</html>
