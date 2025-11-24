<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/order_controller.php';

if (!isLoggedIn() || $_SESSION['user_role'] != 2) {
    header("Location: ../login/login.php");
    exit();
}

$orders = get_all_orders_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - SeamLink Admin</title>
    <link rel="stylesheet" href="../css/app.css">
</head>
<body>
    <?php include __DIR__ . '/includes/admin_menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Manage Orders</h1>
            <p>View and update order statuses</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">All Orders</h3>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--gray-600);">No orders yet.</p>
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
                                    <th style="padding: 12px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr style="border-bottom: 1px solid var(--gray-200);" data-order-id="<?= $order['order_id'] ?>">
                                        <td style="padding: 12px; font-weight: 600;">#<?= htmlspecialchars($order['invoice_no']) ?></td>
                                        <td style="padding: 12px;">
                                            <div style="font-weight: 500;"><?= htmlspecialchars($order['customer_name']) ?></div>
                                            <div style="font-size: 0.875rem; color: var(--gray-600);"><?= htmlspecialchars($order['customer_email']) ?></div>
                                        </td>
                                        <td style="padding: 12px;"><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                        <td style="padding: 12px;">
                                            <select class="status-select" data-order-id="<?= $order['order_id'] ?>" 
                                                    style="padding: 6px 12px; border: 1px solid var(--gray-300); border-radius: 6px; text-transform: capitalize;">
                                                <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $order['order_status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="shipped" <?= $order['order_status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600;">GH₵ <?= number_format($order['order_total'], 2) ?></td>
                                        <td style="padding: 12px; text-align: center;">
                                            <a href="../view/order_details.php?order_id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('.status-select').on('change', function() {
            const orderId = $(this).data('order-id');
            const newStatus = $(this).val();
            const selectElement = $(this);
            
            if (!confirm('Update order status to "' + newStatus + '"?')) {
                // Revert to previous value
                location.reload();
                return;
            }
            
            $.ajax({
                url: '../actions/update_order_action.php',
                method: 'POST',
                data: {
                    order_id: orderId,
                    order_status: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Order status updated successfully!');
                    } else {
                        alert(response.message || 'Failed to update status');
                        location.reload();
                    }
                },
                error: function() {
                    alert('Error updating order status');
                    location.reload();
                }
            });
        });
    </script>
</body>
</html>
