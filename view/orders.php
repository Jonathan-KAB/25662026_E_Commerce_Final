<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/order_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

// Get cart count
$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);

$orders = get_orders_by_customer_ctr($_SESSION['customer_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>My Orders</h1>
            <p>View your order history and status</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="card">
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 64px; margin-bottom: 16px; color: var(--gray-400);"><i class="fas fa-box-open"></i></div>
                        <h3 style="margin-bottom: 12px;">No Orders Yet</h3>
                        <p style="color: var(--gray-600); margin-bottom: 24px;">Start shopping to see your orders here!</p>
                        <a href="all_product.php" class="btn btn-primary">Browse Products</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--gray-200);">
                                    <th style="padding: 12px; text-align: left;">Order #</th>
                                    <th style="padding: 12px; text-align: left;">Date</th>
                                    <th style="padding: 12px; text-align: left;">Status</th>
                                    <th style="padding: 12px; text-align: right;">Total</th>
                                    <th style="padding: 12px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr style="border-bottom: 1px solid var(--gray-200);">
                                        <td style="padding: 12px; font-weight: 600;">#<?= htmlspecialchars($order['invoice_no']) ?></td>
                                        <td style="padding: 12px;"><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                        <td style="padding: 12px;">
                                            <span style="padding: 4px 12px; border-radius: 12px; background: var(--gray-100); text-transform: capitalize; font-size: 0.875rem;">
                                                <?= htmlspecialchars($order['order_status']) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600;">
                                            GH₵ <?= number_format($order['order_total'], 2) ?>
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <a href="order_details.php?order_id=<?= $order['order_id'] ?>" class="btn btn-sm btn-primary">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 24px;">
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>
    <!-- Footer Spacing -->
    <div style="height: 60px;"></div>
</body>
</html>
