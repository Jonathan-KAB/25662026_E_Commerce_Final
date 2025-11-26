<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);
require_once __DIR__ . '/../controllers/order_controller.php';

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$customerId = $_SESSION['customer_id'];

// Get order details
$order = get_order_by_id_ctr($orderId);
$orderItems = get_order_items_ctr($orderId);

// Verify order belongs to customer (or if seller, they sell products in this order)
$userRole = $_SESSION['user_role'] ?? 1;
$canView = false;

if ($userRole == 2) {
    // Admin can view all
    $canView = true;
} elseif ($userRole == 3) {
    // Seller can view if they have products in this order
    foreach ($orderItems as $item) {
        if (isset($item['seller_id']) && $item['seller_id'] == $customerId) {
            $canView = true;
            break;
        }
    }
} elseif ($order && $order['customer_id'] == $customerId) {
    // Buyer can view their own orders
    $canView = true;
}

if (!$canView || !$order) {
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Order Details</h1>
            <p>Order #<?= $order['invoice_no'] ?></p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px;">
            <!-- Order Items -->
            <div>
                <div class="card" style="margin-bottom: 24px;">
                    <div class="card-header">
                        <h3 style="margin: 0;">Order Items</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($orderItems as $item): ?>
                            <div style="display: grid; grid-template-columns: 100px 1fr auto; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--gray-200);">
                                <div>
                                    <?php if ($item['product_image']): ?>
                                        <img src="../<?= htmlspecialchars($item['product_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_title']) ?>"
                                             style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100px; background: var(--gray-200); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--gray-400);">No Image</div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($item['product_title']) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 4px;">
                                        <?= htmlspecialchars($item['cat_name']) ?> / <?= htmlspecialchars($item['brand_name']) ?>
                                    </div>
                                    <div style="font-size: 0.875rem; color: var(--gray-600);">Quantity: <?= $item['qty'] ?></div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 600;">GH₵ <?= number_format($item['product_price'] * $item['qty'], 2) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--gray-600);">GH₵ <?= number_format($item['product_price'], 2) ?> each</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 style="margin: 0;">Shipping Information</h3>
                    </div>
                    <div class="card-body">
                        <div style="line-height: 1.8;">
                            <strong><?= htmlspecialchars($order['shipping_name']) ?></strong><br>
                            <?= htmlspecialchars($order['shipping_address']) ?><br>
                            <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_country']) ?><br>
                            Phone: <?= htmlspecialchars($order['shipping_contact']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="card" style="position: sticky; top: 24px;">
                    <div class="card-header">
                        <h3 style="margin: 0;">Order Summary</h3>
                    </div>
                    <div class="card-body">
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: var(--gray-600);">Order Number</span>
                                <strong>#<?= $order['invoice_no'] ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: var(--gray-600);">Order Date</span>
                                <strong><?= date('M d, Y', strtotime($order['order_date'])) ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: var(--gray-600);">Status</span>
                                <span style="padding: 4px 12px; border-radius: 12px; background: var(--gray-100); text-transform: capitalize; font-size: 0.875rem;">
                                    <?= htmlspecialchars($order['order_status']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="summary-divider"></div>

                        <div style="margin: 20px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Subtotal</span>
                                <span>GH₵ <?= number_format($order['order_total'], 2) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                        </div>

                        <div class="summary-divider"></div>

                        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                            <strong style="font-size: 1.125rem;">Total</strong>
                            <strong style="font-size: 1.125rem; color: var(--primary);">GH₵ <?= number_format($order['order_total'], 2) ?></strong>
                        </div>

                        <a href="orders.php" class="btn btn-outline-secondary btn-block" style="margin-top: 24px;">
                            Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>
</body>
</html>
