<?php
/**
 * Shopping Cart View
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Require login to view cart
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$customerId = (int)$_SESSION['customer_id'];

$cartItems = get_cart_items_ctr($ipAddress, $customerId);
$cartTotal = get_cart_total_ctr($ipAddress, $customerId);
$cartCount = get_cart_count_ctr($ipAddress, $customerId); // Get actual quantity sum
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Shopping Cart</h1>
            <p>Review your items before checkout</p>
        </div>
    </div>

    <div class="container">
        <?php if (empty($cartItems)): ?>
            <div class="card">
                <div class="card-body" style="text-align: center; padding: 60px 20px;">
                    <h3 style="color: var(--gray-600); margin-bottom: 16px;">Your cart is empty</h3>
                    <p style="color: var(--gray-500); margin-bottom: 24px;">Add some products to get started!</p>
                    <a href="all_product.php" class="btn btn-primary">Browse Products</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Cart Actions Bar - Sits above everything -->
            <div class="cart-actions-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 8px 16px; background: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); max-width: fit-content;">
                <div style="font-size: 13px; color: #666; margin-right: 20px;">
                    <strong style="color: #333;"><?= array_sum(array_column($cartItems, 'qty')) ?></strong> item(s) in your cart
                </div>
                <button onclick="emptyCart()" class="btn btn-outline-danger" style="padding: 4px 12px; font-size: 13px; border: 1px solid #dc3545; background: white; color: #dc3545; border-radius: 4px; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                    <span style="margin-right: 4px;">🗑️</span> Empty Cart
                </button>
            </div>

            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-product-id="<?= $item['p_id'] ?>">
                            <div class="cart-item-image">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="../<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_title']) ?>">
                                <?php else: ?>
                                    <div class="no-image">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="cart-item-details">
                                <h3 class="cart-item-title"><?= htmlspecialchars($item['product_title']) ?></h3>
                                <p class="cart-item-meta">
                                    <?= htmlspecialchars($item['cat_name']) ?> / <?= htmlspecialchars($item['brand_name']) ?>
                                </p>
                                <p class="cart-item-price">GH₵ <?= number_format($item['product_price'], 2) ?></p>
                            </div>
                            <div class="cart-item-quantity">
                                <button class="qty-btn qty-decrease" onclick="updateQuantity(<?= $item['p_id'] ?>, -1)">−</button>
                                <input type="number" class="qty-input" value="<?= $item['qty'] ?>" min="1" 
                                       onchange="setQuantity(<?= $item['p_id'] ?>, this.value)" readonly>
                                <button class="qty-btn qty-increase" onclick="updateQuantity(<?= $item['p_id'] ?>, 1)">+</button>
                            </div>
                            <div class="cart-item-subtotal">
                                <p class="subtotal-label">Subtotal</p>
                                <p class="subtotal-price">GH₵ <?= number_format($item['product_price'] * $item['qty'], 2) ?></p>
                            </div>
                            <button class="cart-item-remove" onclick="removeItem(<?= $item['p_id'] ?>)">
                                <span>×</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="card">
                        <div class="card-header">Order Summary</div>
                        <div class="card-body">
                            <div class="summary-row">
                                <span>Items (<?= array_sum(array_column($cartItems, 'qty')) ?>)</span>
                                <span id="subtotal">GH₵ <?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span>TBD</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row summary-total">
                                <span>Total</span>
                                <span id="total">GH₵ <?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <a href="checkout.php" class="btn btn-primary btn-block" style="text-align: center; display: block; text-decoration: none;">
                                Proceed to Checkout
                            </a>
                            <a href="all_product.php" class="btn btn-outline-secondary btn-block" style="margin-top: 12px; text-align: center; display: block; text-decoration: none;">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/cart.js"></script>
    <script>
        function updateQuantity(productId, change) {
            const cartItem = $(`.cart-item[data-product-id="${productId}"]`);
            const qtyInput = cartItem.find('.qty-input');
            let newQty = parseInt(qtyInput.val()) + change;
            
            if (newQty < 1) newQty = 1;
            
            setQuantity(productId, newQty);
        }

        function setQuantity(productId, quantity) {
            quantity = parseInt(quantity);
            if (quantity < 1) quantity = 1;
            
            $.ajax({
                url: '../actions/update_cart_action.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to update cart');
                    }
                },
                error: function() {
                    alert('Error updating cart');
                }
            });
        }

        function removeItem(productId) {
            if (!confirm('Remove this item from cart?')) return;
            
            $.ajax({
                url: '../actions/remove_from_cart_action.php',
                method: 'POST',
                data: { product_id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to remove item');
                    }
                },
                error: function() {
                    alert('Error removing item');
                }
            });
        }

        function emptyCart() {
            if (!confirm('Are you sure you want to empty your cart? This will remove all items.')) {
                return;
            }
            
            $.ajax({
                url: '../actions/empty_cart_action.php',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Cart emptied successfully');
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to empty cart');
                    }
                },
                error: function() {
                    alert('Error emptying cart');
                }
            });
        }

        function proceedToCheckout() {
            <?php if (isLoggedIn()): ?>
                window.location.href = 'checkout.php';
            <?php else: ?>
                if (confirm('You need to login to checkout. Go to login page?')) {
                    window.location.href = '../login/login.php?redirect=view/checkout.php';
                }
            <?php endif; ?>
        }
    </script>
</body>
</html>
