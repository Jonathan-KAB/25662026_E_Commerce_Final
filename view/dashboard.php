<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/customer_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Check if logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$customer = get_customer_by_id_ctr($_SESSION['customer_id']);
$customer_name = $customer['customer_name'] ?? 'User';
$customer_email = $customer['customer_email'] ?? '';
$user_role = $customer['user_role'] ?? 1;

// Get cart count
$ipAddress = $_SERVER['REMOTE_ADDR'];
$cartCount = get_cart_count_ctr($ipAddress, $_SESSION['customer_id']);

// Redirect sellers to seller dashboard
if ($user_role == 3 || $user_role == 4) {
    header('Location: seller_dashboard.php');
    exit();
}

// Fetch recent orders
$orders = get_orders_by_customer_ctr($_SESSION['customer_id']);
$recentOrders = array_slice($orders, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>My Dashboard</h1>
            <p>Manage your account and orders</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px;">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 48px; color: var(--primary); margin-bottom: 16px;"><i class="fas fa-user-circle"></i></div>
                    <h3 class="card-title">Profile</h3>
                    <p style="color: var(--gray-600); margin-bottom: 20px;">Manage your account information</p>
                    <a href="profile.php" class="btn btn-primary">View Profile</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 48px; color: var(--success); margin-bottom: 16px;"><i class="fas fa-box"></i></div>
                    <h3 class="card-title">My Orders</h3>
                    <p style="color: var(--gray-600); margin-bottom: 20px;">Track your order history</p>
                    <a href="orders.php" class="btn btn-primary">View Orders</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 48px; color: var(--warning); margin-bottom: 16px;"><i class="fas fa-shopping-cart"></i></div>
                    <h3 class="card-title">Shopping</h3>
                    <p style="color: var(--gray-600); margin-bottom: 20px;">Continue shopping</p>
                    <a href="all_product.php" class="btn btn-primary">Browse Products</a>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 40px;">
            <div class="card-header">
                <h3 style="margin: 0;">Recent Orders</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recentOrders)): ?>
                    <p style="color: var(--gray-600); text-align: center; padding: 20px;">
                        No orders yet. <a href="all_product.php">Start shopping!</a>
                    </p>
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
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr style="border-bottom: 1px solid var(--gray-200);">
                                        <td style="padding: 12px;">#<?= htmlspecialchars($order['invoice_no']) ?></td>
                                        <td style="padding: 12px;"><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                        <td style="padding: 12px;">
                                            <span style="padding: 4px 12px; border-radius: 12px; background: var(--gray-100); text-transform: capitalize;">
                                                <?= htmlspecialchars($order['order_status']) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600;">
                                            GH₵ <?= number_format($order['order_total'], 2) ?>
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <a href="order_details.php?order_id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($orders) > 5): ?>
                        <div style="text-align: center; margin-top: 20px;">
                            <a href="orders.php" class="btn btn-outline-secondary">View All Orders</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">Account Information</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                    <div>
                        <div style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 4px;">Name</div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($customer_name) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 4px;">Email</div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($customer_email) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 4px;">Contact</div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($customer['customer_contact'] ?? 'N/A') ?></div>
                    </div>
                    <div>
                        <div style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 4px;">Location</div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($customer['customer_city'] ?? 'N/A') ?>, <?= htmlspecialchars($customer['customer_country'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div style="margin-top: 24px;">
                    <a href="profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
