<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/customer_controller.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn() || ($_SESSION['user_role'] != 3 && $_SESSION['user_role'] != 4)) {
    header("Location: ../login/login.php");
    exit();
}

$customer = get_customer_by_id_ctr($_SESSION['customer_id']);
$customer_name = $customer['customer_name'] ?? 'Seller';
$is_service_provider = ($_SESSION['user_role'] == 4);

// Get cart count
$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);

// Fetch seller's products
$sellerProducts = get_products_by_seller_ctr($_SESSION['customer_id']);

// Fetch orders for seller's products
$sellerOrders = get_orders_by_seller_ctr($_SESSION['customer_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1><?= $is_service_provider ? '<i class="fas fa-scissors"></i> Service Provider Dashboard' : 'Seller Dashboard' ?></h1>
            <p><?= $is_service_provider ? 'Manage your service listings and bookings' : 'Manage your products and orders' ?></p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 40px;">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 48px; color: var(--primary); margin-bottom: 16px;"><i class="fas fa-<?= $is_service_provider ? 'list-alt' : 'box' ?>"></i></div>
                    <h3 class="card-title"><?= count($sellerProducts) ?></h3>
                    <p style="color: var(--gray-600);"><?= $is_service_provider ? 'Total Service Listings' : 'Total Products' ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 48px; color: var(--success); margin-bottom: 16px;"><i class="fas fa-shopping-bag"></i></div>
                    <h3 class="card-title"><?= count($sellerOrders) ?></h3>
                    <p style="color: var(--gray-600);"><?= $is_service_provider ? 'Total Bookings' : 'Total Orders' ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 48px; color: var(--warning); margin-bottom: 16px;"><i class="fas fa-money-bill-wave"></i></div>
                    <h3 class="card-title">GH₵ <?= number_format(array_sum(array_column($sellerOrders, 'order_total')), 2) ?></h3>
                    <p style="color: var(--gray-600);"><?= $is_service_provider ? 'Total Revenue' : 'Total Sales' ?></p>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 40px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;"><?= $is_service_provider ? '<i class="fas fa-scissors"></i> My Service Listings' : 'My Products' ?></h3>
                <a href="../actions/seller_add_product.php" class="btn btn-primary" style="<?= $is_service_provider ? 'background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);' : '' ?>">
                    <i class="fas fa-plus-circle"></i> <?= $is_service_provider ? 'Add New Service' : 'Add New Product' ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($sellerProducts)): ?>
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 64px; margin-bottom: 16px; color: var(--gray-400);"><i class="fas fa-<?= $is_service_provider ? 'clipboard-list' : 'box-open' ?>"></i></div>
                        <h3 style="margin-bottom: 12px;"><?= $is_service_provider ? 'No Service Listings Yet' : 'No Products Yet' ?></h3>
                        <p style="color: var(--gray-600); margin-bottom: 24px;">
                            <?= $is_service_provider ? 'Start by adding your first service offering!' : 'Start by adding your first product!' ?>
                        </p>
                        <a href="../actions/seller_add_product.php" class="btn btn-primary" style="<?= $is_service_provider ? 'background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);' : '' ?>">
                            <i class="fas fa-plus-circle"></i> <?= $is_service_provider ? 'Add Service' : 'Add Product' ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--gray-200);">
                                    <th style="padding: 12px; text-align: left;"><?= $is_service_provider ? 'Service' : 'Product' ?></th>
                                    <th style="padding: 12px; text-align: left;"><?= $is_service_provider ? 'Type' : 'Category' ?></th>
                                    <th style="padding: 12px; text-align: right;"><?= $is_service_provider ? 'Starting Price' : 'Price' ?></th>
                                    <th style="padding: 12px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sellerProducts as $product): ?>
                                    <tr style="border-bottom: 1px solid var(--gray-200);">
                                        <td style="padding: 12px;">
                                            <div style="display: flex; gap: 12px; align-items: center;">
                                                <?php if ($product['product_image']): ?>
                                                    <img src="../<?= htmlspecialchars($product['product_image']) ?>" 
                                                         alt="<?= htmlspecialchars($product['product_title']) ?>"
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                <?php endif; ?>
                                                <div>
                                                    <div style="font-weight: 600;"><?= htmlspecialchars($product['product_title']) ?></div>
                                                    <div style="font-size: 0.875rem; color: var(--gray-600);"><?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($product['cat_name'] ?? 'N/A') ?></td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600;">
                                            <?= $is_service_provider ? 'From ' : '' ?>GH₵ <?= number_format($product['product_price'], 2) ?>
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <a href="../actions/seller_edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;"><?= $is_service_provider ? '<i class="fas fa-calendar-check"></i> Recent Bookings' : 'Recent Orders' ?></h3>
            </div>
            <div class="card-body">
                <?php if (empty($sellerOrders)): ?>
                    <p style="color: var(--gray-600); text-align: center; padding: 20px;">
                        <?= $is_service_provider ? 'No bookings yet.' : 'No orders yet.' ?>
                    </p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--gray-200);">
                                    <th style="padding: 12px; text-align: left;">Order #</th>
                                    <th style="padding: 12px; text-align: left;">Customer</th>
                                    <th style="padding: 12px; text-align: left;">Date</th>
                                    <th style="padding: 12px; text-align: left;">Status</th>
                                    <th style="padding: 12px; text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($sellerOrders, 0, 10) as $order): ?>
                                    <tr style="border-bottom: 1px solid var(--gray-200);">
                                        <td style="padding: 12px; font-weight: 600;">#<?= htmlspecialchars($order['invoice_no']) ?></td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td style="padding: 12px;"><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                        <td style="padding: 12px;">
                                            <span style="padding: 4px 12px; border-radius: 12px; background: var(--gray-100); text-transform: capitalize; font-size: 0.875rem;">
                                                <?= htmlspecialchars($order['order_status']) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600;">GH₵ <?= number_format($order['order_total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
