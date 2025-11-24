<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);
require_once __DIR__ . '/../controllers/order_controller.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ../login/login.php');
    exit;
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: all_product.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$customerId = $_SESSION['customer_id'];

// Get order details
$order = get_order_by_id_ctr($orderId);
$orderItems = get_order_items_ctr($orderId);

// Verify order belongs to customer
if (!$order || $order['customer_id'] != $customerId) {
    header('Location: all_product.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <style>
        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: white;
            font-size: 40px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="container" style="margin-top: 60px; margin-bottom: 60px; max-width: 800px;">
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 48px 32px;">
                <div class="success-icon">✓</div>
                <h1 style="margin-bottom: 12px;">Order Confirmed!</h1>
                <p style="color: var(--gray-600); margin-bottom: 32px;">Thank you for your order. We'll send you a confirmation email shortly.</p>
                
                <div style="background: var(--gray-50); padding: 24px; border-radius: var(--radius-lg); margin-bottom: 32px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: left;">
                        <div>
                            <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 4px;">Order Number</div>
                            <div style="font-weight: 600;">#<?= $order['invoice_no'] ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 4px;">Order Date</div>
                            <div style="font-weight: 600;"><?= date('M d, Y', strtotime($order['order_date'])) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 4px;">Total Amount</div>
                            <div style="font-weight: 600;">GH₵ <?= number_format($order['order_total'], 2) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 4px;">Status</div>
                            <div style="font-weight: 600; text-transform: capitalize;"><?= $order['order_status'] ?></div>
                        </div>
                    </div>
                </div>

                <div style="text-align: left; margin-bottom: 32px;">
                    <h3 style="margin-bottom: 16px;">Order Items</h3>
                    <?php foreach ($orderItems as $item): ?>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--gray-200);">
                            <div>
                                <div style="font-weight: 500;"><?= htmlspecialchars($item['product_title']) ?></div>
                                <div style="font-size: 0.875rem; color: var(--gray-600);">Qty: <?= $item['qty'] ?></div>
                            </div>
                            <div style="font-weight: 600;">GH₵ <?= number_format($item['product_price'] * $item['qty'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="text-align: left; margin-bottom: 32px;">
                    <h3 style="margin-bottom: 16px;">Shipping Information</h3>
                    <div style="color: var(--gray-700);">
                        <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                        <?= htmlspecialchars($order['customer_city']) ?>, <?= htmlspecialchars($order['customer_country']) ?><br>
                        <?= htmlspecialchars($order['customer_contact']) ?><br>
                        <?= htmlspecialchars($order['customer_email']) ?>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: center;">
                    <a href="orders.php" class="btn btn-primary">View All Orders</a>
                    <a href="all_product.php" class="btn btn-outline-secondary">Continue Shopping</a>
                </div>
                
                <div id="autoRedirect" style="margin-top: 24px; font-size: 0.875rem; color: var(--gray-600);">
                    Redirecting to shop in <span id="countdown">10</span> seconds...
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-redirect to all_product.php after 10 seconds
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'all_product.php';
            }
        }, 1000);
        
        // Clear sessionStorage checkout data
        sessionStorage.removeItem('checkout_cart');
    </script>
</body>
</html>
