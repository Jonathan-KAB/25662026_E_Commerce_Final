<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../settings/db_class.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: all_product.php');
    exit;
}

// Fetch product details
$product = view_single_product_ctr($product_id);

if (!$product) {
    header('Location: all_product.php');
    exit;
}

// Fetch seller information if product has a seller
$seller_info = null;
if (!empty($product['seller_id'])) {
    $db = new db_connection();
    $db->db_connect();
    
    // Simple query - just get customer info
    $seller_sql = "SELECT customer_id, customer_name, customer_email, customer_image
                   FROM customer
                   WHERE customer_id = {$product['seller_id']}";
    
    $seller_info = $db->db_fetch_one($seller_sql);
    
    // Add default values for compatibility
    if ($seller_info) {
        $seller_info['store_name'] = null;
        $seller_info['store_logo'] = null;
        $seller_info['rating_average'] = 0;
        $seller_info['verified'] = false;
    }
}

// Fetch reviews for this product
$db = new db_connection();
$db->db_connect();

// Check if product_reviews table exists
$check_table = $db->db_fetch_one("SHOW TABLES LIKE 'product_reviews'");

if ($check_table) {
    // Table exists, fetch reviews
    // First check what columns exist
    $columns = $db->db_fetch_all("SHOW COLUMNS FROM product_reviews");
    $has_created_at = false;
    $has_status = false;
    $has_verified = false;
    $has_helpful = false;
    $has_title = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] == 'created_at') $has_created_at = true;
        if ($col['Field'] == 'status') $has_status = true;
        if ($col['Field'] == 'verified_purchase') $has_verified = true;
        if ($col['Field'] == 'helpful_count') $has_helpful = true;
        if ($col['Field'] == 'review_title') $has_title = true;
    }
    
    // Build query based on available columns
    $date_field = $has_created_at ? "DATE_FORMAT(pr.created_at, '%M %d, %Y')" : "DATE_FORMAT(pr.review_date, '%M %d, %Y')";
    $order_field = $has_created_at ? "pr.created_at" : "pr.review_date";
    $status_filter = $has_status ? "AND pr.status = 'approved'" : "";
    
    $reviews_sql = "SELECT pr.*, 
                    c.customer_name,
                    $date_field as review_date
                    FROM product_reviews pr
                    JOIN customer c ON pr.customer_id = c.customer_id
                    WHERE pr.product_id = $product_id 
                    $status_filter
                    ORDER BY $order_field DESC";
    
    $reviews = $db->db_fetch_all($reviews_sql);
    if ($reviews === false) {
        $reviews = [];
    }
    
    // Add default values for missing columns
    foreach ($reviews as &$review) {
        if (!$has_verified) $review['verified_purchase'] = false;
        if (!$has_helpful) $review['helpful_count'] = 0;
        if (!$has_title) $review['review_title'] = '';
    }
    unset($review);
} else {
    // Table doesn't exist yet
    $reviews = [];
}

// Calculate rating breakdown
$rating_breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$total_reviews = count($reviews);
$verified_count = 0;

foreach ($reviews as $review) {
    if (isset($review['rating']) && $review['rating'] >= 1 && $review['rating'] <= 5) {
        $rating_breakdown[(int)$review['rating']]++;
    }
    if (isset($review['verified_purchase']) && $review['verified_purchase']) {
        $verified_count++;
    }
}

// Check if user has already reviewed this product
$has_reviewed = false;
if (isset($_SESSION['customer_id']) && $check_table) {
    $check_sql = "SELECT review_id FROM product_reviews 
                  WHERE product_id = $product_id 
                  AND customer_id = {$_SESSION['customer_id']}";
    $existing = $db->db_fetch_one($check_sql);
    $has_reviewed = ($existing !== false && $existing !== null && !empty($existing));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_title']) ?> - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #666;
            margin: 0 5px;
        }

        /* Product Detail Container */
        .product-detail {
            background-color: #fff !important;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            min-height: 500px;
        }

        /* Product Image Section */
        .product-image-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .main-product-image {
            width: 100%;
            max-width: 500px;
            height: 500px;
            border-radius: 8px;
            overflow: hidden;
            background-color: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .main-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Product Info Section */
        .product-info-section {
            display: flex;
            flex-direction: column;
        }

        .product-category-badge {
            display: inline-block;
            padding: 5px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 15px;
            width: fit-content;
        }

        .product-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .product-brand {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .product-brand strong {
            color: #333;
        }

        /* Product price styling is handled via `css/app.css`.
           Use `.product-price.hero` for the single-product hero block. */

        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }

        .product-description-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .product-description {
            font-size: 15px;
            color: #666;
            line-height: 1.6;
        }

        .product-keywords {
            margin-bottom: 30px;
        }

        .keywords-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .keyword-tag {
            padding: 5px 12px;
            background-color: #f0f0f0;
            color: #555;
            border-radius: 15px;
            font-size: 13px;
        }

        .product-id-info {
            font-size: 13px;
            color: #999;
            margin-bottom: 20px;
        }

        .product-seller {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .seller-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .seller-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .seller-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .seller-info {
            flex: 1;
        }

        .seller-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }

        .seller-details {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .seller-name {
            font-weight: 600;
            color: #007bff;
            text-decoration: none;
        }

        .seller-name:hover {
            text-decoration: underline;
        }

        .seller-verified {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
        }

        .seller-rating {
            color: #ffc107;
            font-size: 14px;
        }

        /* Action Buttons - place CTAs immediately after content (not bottom-aligned) to reduce visual gap */
        .product-actions { display: flex; gap: 16px; margin-top: 12px; }

        /* Reviews Section Styles */
        .reviews-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: 30px;
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .rating-summary {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .average-rating {
            text-align: center;
        }

        .average-rating-number {
            font-size: 48px;
            font-weight: bold;
            color: #333;
        }

        .stars {
            color: #ffc107;
            font-size: 24px;
        }

        .review-count {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .verified-purchase-count {
            color: #28a745;
            margin-left: 5px;
        }

        .rating-bars {
            flex: 1;
            min-width: 250px;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .rating-bar-label {
            width: 60px;
            font-size: 14px;
            color: #666;
        }

        .rating-bar-track {
            flex: 1;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: #ffc107;
        }

        .rating-bar-count {
            width: 40px;
            text-align: right;
            font-size: 14px;
            color: #666;
        }

        .write-review-btn {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .write-review-btn:hover {
            background: #0056b3;
        }

        .write-review-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Review Form */
        .review-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: none;
        }

        .review-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .star-rating {
            display: flex;
            gap: 5px;
            font-size: 32px;
            cursor: pointer;
        }

        .star-rating span {
            color: #ddd;
            transition: color 0.2s;
        }

        .star-rating span.active,
        .star-rating span:hover,
        .star-rating span:hover ~ span {
            color: #ffc107;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 12px;
        }

        .btn-submit,
        .btn-cancel {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit {
            background: #28a745;
            color: white;
        }

        .btn-submit:hover {
            background: #218838;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        /* Review List */
        .reviews-list {
            margin-top: 30px;
        }

        .review-item {
            padding: 24px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reviewer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .reviewer-details h4 {
            margin: 0 0 4px 0;
            font-size: 16px;
            color: #333;
        }

        .review-meta {
            font-size: 13px;
            color: #666;
        }

        .verified-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-left: 8px;
        }

        .review-rating {
            color: #ffc107;
            font-size: 16px;
        }

        .review-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
            color: #333;
        }

        .review-text {
            color: #666;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .review-date {
            font-size: 13px;
            color: #999;
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .product-title {
                font-size: 24px;
            }

            .product-price.hero {
                font-size: 28px;
            }

            .main-product-image {
                height: 300px;
            }

            .product-actions {
                flex-direction: column;
            }

            .rating-summary {
                flex-direction: column;
                gap: 20px;
            }

            .reviews-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><?= htmlspecialchars($product['product_title']) ?></h1>
            <p><?= htmlspecialchars($product['cat_name'] ?? 'Fabric Details') ?></p>
        </div>
    </div>

    <div class="container">
        <!-- Product Detail -->
        <div class="product-detail">
            <!-- Product Image Section -->
            <div class="product-image-section">
                <div class="main-product-image">
                        <?php if (!empty($product['product_image'])): ?>
                            <?php 
                            // Handle image paths for both local XAMPP and Ashesi server
                            $imagePath = $product['product_image'];
                            // If path already starts with /uploads (absolute from root)
                            if (strpos($imagePath, '/uploads') === 0) {
                                echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['product_title']) . '">';
                            } elseif (strpos($imagePath, 'uploads/') === 0) {
                                echo '<img src="../' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['product_title']) . '">';
                            } else {
                                echo '<img src="../' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['product_title']) . '">';
                            }
                            ?>
                        <?php else: ?>
                            <div role="img" aria-label="No image available" class="product-image-placeholder">
                                <i class="fas fa-image" style="font-size:64px;color:#9ca3af;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="product-info-section">
                    <h1 class="product-title"><?= htmlspecialchars($product['product_title']) ?></h1>

                <div class="product-brand">
                    <strong>Vendor:</strong> <?php if (!empty($product['seller_id'])): ?> <a href="seller_profile.php?id=<?= (int)$product['seller_id'] ?>"><?= htmlspecialchars($product['seller_name'] ?? $product['brand_name'] ?? 'Unknown') ?></a><?php else: ?><?= htmlspecialchars($product['brand_name'] ?? 'Unknown') ?><?php endif; ?>
                </div>

                <?php
                    // Compute fallback seller display values
                    $displaySellerName = $seller_info['customer_name'] ?? ($product['seller_name'] ?? $product['brand_name'] ?? 'Unknown');
                    $displaySellerImage = $seller_info['customer_image'] ?? ($product['seller_image'] ?? '');
                    $displaySellerId = $seller_info['customer_id'] ?? ($product['seller_id'] ?? 0);
                ?>
                <div class="product-seller">
                    <div class="seller-content">
                        <?php if (!empty($displaySellerImage)): ?>
                            <?php
                            $imagePath = $displaySellerImage;
                            if (strpos($imagePath, '/uploads') === 0) {
                                $imageSrc = htmlspecialchars($imagePath);
                            } elseif (strpos($imagePath, 'uploads/') === 0) {
                                $imageSrc = '../' . htmlspecialchars($imagePath);
                            } else {
                                $imageSrc = '../' . htmlspecialchars($imagePath);
                            }
                            ?>
                            <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($displaySellerName) ?>" class="seller-logo">
                        <?php else: ?>
                            <div class="seller-avatar"><?= strtoupper(substr($displaySellerName, 0, 1)) ?></div>
                        <?php endif; ?>
                        <div class="seller-info">
                            <div class="seller-label">Supplied by</div>
                            <div class="seller-details">
                                <?php if (!empty($displaySellerId)): ?>
                                    <a href="seller_profile.php?id=<?= (int)$displaySellerId ?>" class="seller-name"><?= htmlspecialchars($displaySellerName) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($displaySellerName) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-price hero <?= ($product['product_type'] ?? '') === 'service' ? 'service' : '' ?>">GH₵ <?= number_format($product['product_price'], 2) ?></div>

                <!-- Stock Availability -->
                <?php if (isset($product['product_stock'])): ?>
                    <?php if ($product['product_stock'] >= 999): ?>
                        <div class="stock-bubble production"><i class="fas fa-check-circle"></i> Available for Production</div>
                        <div style="color: #6b21a8; font-size: 16px; margin-top: 6px;">This service provider is ready to fulfill your custom orders</div>
                    <?php elseif ($product['product_stock'] > 0 && $product['product_stock'] <= 10): ?>
                        <div class="stock-bubble warn"><i class="fas fa-exclamation-triangle"></i> Only <strong><?= $product['product_stock'] ?></strong> units remaining</div>
                    <?php elseif ($product['product_stock'] > 10): ?>
                        <div class="stock-bubble in-stock"><i class="fas fa-check-circle"></i> <strong><?= $product['product_stock'] ?></strong> units available</div>
                    <?php else: ?>
                        <div class="stock-bubble out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Product ID hidden in single product view for privacy/UX -->
                <!-- <div class="product-id-info">Product ID: #<?= $product['product_id'] ?></div> -->

                <div class="divider"></div>

                <!-- Product Description -->
                <?php if (!empty($product['product_desc'])): ?>
                    <div class="product-description-section">
                        <h2 class="section-title">Fabric Description</h2>
                        <p class="product-description"><?= nl2br(htmlspecialchars($product['product_desc'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Product Keywords -->
                <?php if (!empty($product['product_keywords'])): ?>
                    <div class="product-keywords">
                        <h2 class="section-title">Tags</h2>
                        <div class="keywords-list">
                            <?php 
                            $keywords = explode(',', $product['product_keywords']);
                            foreach ($keywords as $keyword): 
                                $keyword = trim($keyword);
                                if (!empty($keyword)):
                            ?>
                                <span class="keyword-tag"><?= htmlspecialchars($keyword) ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="product-actions">
                    <button class="add-to-cart-btn cta-btn primary" onclick="addToCart(<?= $product['product_id'] ?>)"
                        <?= (isset($product['product_stock']) && $product['product_stock'] <= 0) ? 'disabled' : '' ?>>
                        <i class="fas fa-shopping-cart"></i>
                        <?= (isset($product['product_stock']) && $product['product_stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
                    </button>
                    <a href="all_product.php" class="back-btn cta-btn secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Store
                    </a>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <div class="reviews-header">
                <div class="rating-summary">
                    <div class="average-rating">
                        <div class="average-rating-number"><?= number_format($product['rating_average'] ?? 0, 1) ?></div>
                        <div class="stars">
                            <?php
                            $avg = $product['rating_average'] ?? 0;
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= round($avg) ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <div class="review-count">
                            <?= $total_reviews ?> review<?= $total_reviews != 1 ? 's' : '' ?>
                            <?php if ($verified_count > 0): ?>
                                <span class="verified-purchase-count">(<?= $verified_count ?> verified purchase<?= $verified_count != 1 ? 's' : '' ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($total_reviews > 0): ?>
                    <div class="rating-bars">
                        <?php for ($i = 5; $i >= 1; $i--): 
                            $count = $rating_breakdown[$i];
                            $percentage = $total_reviews > 0 ? ($count / $total_reviews * 100) : 0;
                        ?>
                        <div class="rating-bar">
                            <span class="rating-bar-label"><?= $i ?> star<?= $i != 1 ? 's' : '' ?></span>
                            <div class="rating-bar-track">
                                <div class="rating-bar-fill" style="width: <?= $percentage ?>%"></div>
                            </div>
                            <span class="rating-bar-count"><?= $count ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['customer_id'])): ?>
                    <?php if ($has_reviewed): ?>
                        <button class="write-review-btn" disabled>You've Already Reviewed</button>
                    <?php else: ?>
                        <button class="write-review-btn" onclick="toggleReviewForm()">Write a Review</button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../login/login.php" class="write-review-btn" style="text-decoration: none; display: inline-block;">Login to Review</a>
                <?php endif; ?>
            </div>

            <!-- Review Form -->
            <?php if (isset($_SESSION['customer_id'])): ?>
            <div id="reviewForm" class="review-form" data-has-reviewed="<?= $has_reviewed ? 'true' : 'false' ?>" <?= $has_reviewed ? 'style="display: none;"' : '' ?>>
                <h3 style="margin-bottom: 20px;">Write Your Review</h3>
                
                <div id="reviewMessage"></div>

                <form id="submitReviewForm">
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Rating *</label>
                        <div class="star-rating" id="starRating">
                            <span data-rating="1">☆</span>
                            <span data-rating="2">☆</span>
                            <span data-rating="3">☆</span>
                            <span data-rating="4">☆</span>
                            <span data-rating="5">☆</span>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reviewTitle">Review Title</label>
                        <input type="text" class="form-input" id="reviewTitle" name="review_title" 
                               placeholder="Sum up your experience" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reviewText">Review *</label>
                        <textarea class="form-textarea" id="reviewText" name="review_text" 
                                  placeholder="Share your thoughts about this product (minimum 10 characters)" 
                                  required minlength="10"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Submit Review</button>
                        <button type="button" class="btn-cancel" onclick="toggleReviewForm()">Cancel</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <div class="reviews-list">
                <h3 style="margin-bottom: 20px;">Customer Reviews</h3>
                
                    <?php if (empty($reviews)): ?>
                    <div class="no-reviews">
                        <div style="font-size: 48px; margin-bottom: 12px;"><i class="fas fa-comments" style="color:#9ca3af;"></i></div>
                        <h4 style="margin-bottom: 8px; color: #666;">No reviews yet</h4>
                        <p>Be the first to share your thoughts about this product!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">
                                    <?= strtoupper(substr($review['customer_name'], 0, 1)) ?>
                                </div>
                                <div class="reviewer-details">
                                    <h4>
                                        <?= htmlspecialchars($review['customer_name']) ?>
                                        <?php if (isset($review['verified_purchase']) && $review['verified_purchase']): ?>
                                            <span class="verified-badge"><i class="fas fa-check-circle" aria-hidden="true" style="margin-right:6px;"></i> Verified Purchase</span>
                                        <?php endif; ?>
                                    </h4>
                                    <div class="review-meta">
                                        <span class="review-rating">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $review['rating'] ? '★' : '☆';
                                            }
                                            ?>
                                        </span>
                                        <span class="review-date"> • <?= htmlspecialchars($review['review_date']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($review['review_title']) && !empty($review['review_title'])): ?>
                            <div class="review-title"><?= htmlspecialchars($review['review_title']) ?></div>
                        <?php endif; ?>

                        <div class="review-text">
                            <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                        </div>

                        <?php if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] == $review['customer_id']): ?>
                        <div class="review-actions" style="margin-top: 12px; display: flex; gap: 8px;">
                            <button class="btn-edit-review" 
                                    data-review-id="<?= $review['review_id'] ?>"
                                    data-rating="<?= $review['rating'] ?>"
                                    data-title="<?= htmlspecialchars($review['review_title'] ?? '') ?>"
                                    data-text="<?= htmlspecialchars($review['review_text']) ?>"
                                    style="padding: 6px 12px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-delete-review" 
                                    data-review-id="<?= $review['review_id'] ?>"
                                    style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($review['helpful_count']) && $review['helpful_count'] > 0): ?>
                            <div style="font-size: 13px; color: #666;">
                                <?= $review['helpful_count'] ?> person<?= $review['helpful_count'] != 1 ? 's' : '' ?> found this helpful
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div> <!-- End container -->

    <!-- Load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Star rating functionality
        let selectedRating = 0;
        
        const starRatingElement = document.getElementById('starRating');
        
        if (starRatingElement) {
            document.querySelectorAll('#starRating span').forEach(star => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.getAttribute('data-rating'));
                    document.getElementById('ratingValue').value = selectedRating;
                    updateStars();
                });
                
                star.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.getAttribute('data-rating'));
                    highlightStars(rating);
                });
            });
            
            starRatingElement.addEventListener('mouseleave', function() {
                updateStars();
            });
        }
        
        function highlightStars(rating) {
            document.querySelectorAll('#starRating span').forEach(star => {
                const starRating = parseInt(star.getAttribute('data-rating'));
                if (starRating <= rating) {
                    star.classList.add('active');
                    star.textContent = '★';
                } else {
                    star.classList.remove('active');
                    star.textContent = '☆';
                }
            });
        }
        
        function updateStars() {
            highlightStars(selectedRating);
        }
        
        // Reset form to add mode
        function resetReviewForm() {
            const formElement = document.getElementById('submitReviewForm');
            const submitBtn = document.querySelector('#submitReviewForm button[type="submit"]');
            const existingInput = document.getElementById('editReviewId');
            const form = document.getElementById('reviewForm');
            const formHeading = form?.querySelector('h3');
            
            if (!formElement) return;
            
            // Remove editing mode
            formElement.dataset.editing = 'false';
            if (existingInput) {
                existingInput.remove();
            }
            
            // Reset heading and button text
            if (formHeading) formHeading.textContent = 'Write Your Review';
            if (submitBtn) {
                submitBtn.innerHTML = 'Submit Review';
            }
            
            // Clear form
            selectedRating = 0;
            const ratingValueEl = document.getElementById('ratingValue');
            const reviewTitleEl = document.getElementById('reviewTitle');
            const reviewTextEl = document.getElementById('reviewText');
            
            if (ratingValueEl) ratingValueEl.value = '';
            if (reviewTitleEl) reviewTitleEl.value = '';
            if (reviewTextEl) reviewTextEl.value = '';
            updateStars();
        }
        
        // Toggle review form
        function toggleReviewForm() {
            const form = document.getElementById('reviewForm');
            if (!form) return;
            
            const hasReviewed = form.dataset.hasReviewed === 'true';
            const formElement = document.getElementById('submitReviewForm');
            const isEditing = formElement?.dataset.editing === 'true';
            
            if (form.style.display === 'none' || !form.classList.contains('active')) {
                form.style.display = 'block';
                form.classList.add('active');
                resetReviewForm(); // Reset when opening
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                form.classList.remove('active');
                // If user has reviewed and was editing, hide the form
                if (hasReviewed && isEditing) {
                    form.style.display = 'none';
                }
                resetReviewForm(); // Reset form state
            }
        }
        
        // Submit review
        document.getElementById('submitReviewForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            if (selectedRating === 0) {
                showMessage('Please select a rating', 'error');
                return;
            }

            const formData = new FormData(this);
            const isEditing = this.dataset.editing === 'true';
            const actionUrl = isEditing ? '../actions/update_review_action.php' : '../actions/add_review_action.php';

            // Disable submit button to prevent double submissions
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                var previousBtnHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = 'Submitting...';
            }

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showMessage((isEditing ? 'Review updated' : 'Review submitted') + ' successfully! Refreshing page...', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showMessage(response.message || 'Failed to submit review', 'error');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = previousBtnHtml;
                        }
                    }
                },
                error: function(xhr) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        showMessage(response.message || 'Error submitting review', 'error');
                    } catch (e) {
                        showMessage('Error submitting review. Please try again.', 'error');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = previousBtnHtml;
                    }
                }
            });
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('reviewMessage');
            messageDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Initialize edit/delete button handlers using jQuery ready
        $(document).ready(function() {
            console.log('DOM ready - checking for buttons');
            console.log('Edit buttons found:', $('.btn-edit-review').length);
            console.log('Delete buttons found:', $('.btn-delete-review').length);
            
            // Edit review
            $('.btn-edit-review').on('click', function() {
                console.log('Edit button clicked');
                const reviewId = $(this).data('review-id');
                const rating = $(this).data('rating');
                const title = $(this).data('title');
                const text = $(this).data('text');

                console.log('Review data:', { reviewId, rating, title, text });

                // Get form elements
                const ratingValueEl = document.getElementById('ratingValue');
                const reviewTitleEl = document.getElementById('reviewTitle');
                const reviewTextEl = document.getElementById('reviewText');
                const form = document.getElementById('reviewForm');
                const submitBtn = document.querySelector('#submitReviewForm button[type="submit"]');
                const formElement = document.getElementById('submitReviewForm');
                const formHeading = form.querySelector('h3');

                // Check if form elements exist
                if (!ratingValueEl || !reviewTitleEl || !reviewTextEl || !form || !submitBtn || !formElement) {
                    console.error('Review form elements not found');
                    alert('Error: Review form not available. Please make sure you are logged in.');
                    return;
                }

                // Populate form with existing values
                selectedRating = parseInt(rating);
                ratingValueEl.value = rating;
                updateStars();
                reviewTitleEl.value = title;
                reviewTextEl.value = text;

                // Show form
                form.style.display = 'block';
                form.classList.add('active');
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                // Change heading and submit button
                if (formHeading) formHeading.textContent = 'Edit Your Review';
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Review';
                
                // Remove existing review_id input if any
                const existingInput = document.getElementById('editReviewId');
                if (existingInput) {
                    existingInput.remove();
                }
                
                const reviewIdInput = document.createElement('input');
                reviewIdInput.type = 'hidden';
                reviewIdInput.name = 'review_id';
                reviewIdInput.value = reviewId;
                reviewIdInput.id = 'editReviewId';
                formElement.appendChild(reviewIdInput);

                // Update form submission handler
                formElement.dataset.editing = 'true';
            });

            // Delete review
            $('.btn-delete-review').on('click', function() {
                console.log('Delete button clicked');
                
                if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
                    return;
                }

                const reviewId = $(this).data('review-id');
                
                console.log('Deleting review:', reviewId);

                $.ajax({
                    url: '../actions/delete_review_action.php',
                    method: 'POST',
                    data: { review_id: reviewId },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Delete response:', response);
                        if (response.status === 'success') {
                            alert('Review deleted successfully!');
                            location.reload();
                        } else {
                            alert(response.message || 'Failed to delete review');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete error:', error);
                        alert('Error deleting review. Please try again.');
                    }
                });
            });
        });
        
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
                        const btn = document.querySelector('.add-to-cart-btn');
                        const originalText = btn.textContent;
                        btn.textContent = 'Added to Cart!';
                        btn.disabled = true;
                        
                        setTimeout(function() {
                            btn.textContent = originalText;
                            btn.disabled = false;
                        }, 2000);
                        
                        // Update cart count if badge exists
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
    </script>
        <!-- Footer Spacing -->
        <div style="height: 60px;"></div>
    </body>
    </html>
