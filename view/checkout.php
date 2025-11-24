<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$ipAddress = $_SERVER['REMOTE_ADDR'];
$customerId = $_SESSION['customer_id'];

// Get cart items
$cartItems = get_cart_items_ctr($ipAddress, $customerId);
$cartTotal = get_cart_total_ctr($ipAddress, $customerId);
$cartCount = count($cartItems);

// Redirect if cart is empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Get customer info
require_once __DIR__ . '/../controllers/customer_controller.php';
$customer = get_customer_by_id_ctr($customerId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <style>
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 0.875rem;
            color: var(--gray-700);
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Checkout</h1>
            <p>Complete your order</p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="cart-container">
            <!-- Checkout Form -->
            <div>
                <div class="card">
                    <div class="card-header" style="background: var(--gray-50); border-bottom: 1px solid var(--gray-200); padding: 16px 20px;">
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0; color: var(--gray-900);">Shipping Information</h3>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <form id="checkout-form" method="POST" action="../actions/checkout_action.php">
                            <div style="display: grid; gap: 20px;">
                                <div class="form-group">
                                    <label for="shipping_name" class="form-label">Full Name</label>
                                    <input type="text" id="shipping_name" name="shipping_name" 
                                           value="<?= htmlspecialchars($customer['customer_name'] ?? '') ?>"
                                           required class="form-input"
                                           style="width: 100%; padding: 10px 14px; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.9375rem;">
                                </div>

                                <div class="form-group">
                                    <label for="shipping_contact" class="form-label">Phone Number</label>
                                    <input type="tel" id="shipping_contact" name="shipping_contact" 
                                           value="<?= htmlspecialchars($customer['customer_contact'] ?? '') ?>"
                                           required class="form-input"
                                           style="width: 100%; padding: 10px 14px; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.9375rem;">
                                </div>

                                <div class="form-group">
                                    <label for="shipping_address" class="form-label">Street Address</label>
                                    <textarea id="shipping_address" name="shipping_address" 
                                              required class="form-input" rows="3"
                                              style="width: 100%; padding: 10px 14px; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.9375rem; resize: vertical;"><?= htmlspecialchars($customer['customer_address'] ?? '') ?></textarea>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div class="form-group">
                                        <label for="shipping_city" class="form-label">City</label>
                                        <input type="text" id="shipping_city" name="shipping_city" 
                                               value="<?= htmlspecialchars($customer['customer_city'] ?? '') ?>"
                                               required class="form-input"
                                               style="width: 100%; padding: 10px 14px; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.9375rem;">
                                    </div>

                                    <div class="form-group">
                                        <label for="shipping_country" class="form-label">Country</label>
                                        <input type="text" id="shipping_country" name="shipping_country" 
                                               value="<?= htmlspecialchars($customer['customer_country'] ?? '') ?>"
                                               required class="form-input"
                                               style="width: 100%; padding: 10px 14px; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.9375rem;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary">
                <div class="card">
                    <div class="card-header" style="background: var(--gray-50); border-bottom: 1px solid var(--gray-200); padding: 16px 20px;">
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0; color: var(--gray-900);">Order Summary</h3>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <div style="margin-bottom: 20px;">
                            <?php foreach ($cartItems as $item): ?>
                                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--gray-200);">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500; margin-bottom: 4px;"><?= htmlspecialchars($item['product_title']) ?></div>
                                        <div style="font-size: 0.875rem; color: var(--gray-600);">Qty: <?= $item['qty'] ?></div>
                                    </div>
                                    <div style="font-weight: 600;">
                                        GH₵ <?= number_format($item['product_price'] * $item['qty'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-divider"></div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>GH₵ <?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span id="totalAmount">GH₵ <?= number_format($cartTotal, 2) ?></span>
                        </div>

                        <button type="button" id="proceedToCheckout" class="btn btn-primary btn-block" style="margin-top: 24px;">
                            Pay with Paystack
                        </button>
                        
                        <!-- Hidden button that shows after PayStack redirect -->
                        <button type="button" id="verifyPayment" class="btn btn-success btn-block" style="margin-top: 12px; display: none;">
                            ✓ I've Completed Payment - Verify Now
                        </button>
                        
                        <a href="cart.php" class="btn btn-outline-secondary btn-block" style="margin-top: 12px;">
                            Back to Cart
                        </a>
                        
                        <div id="paymentInstructions" style="display: none; margin-top: 16px; padding: 12px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; font-size: 0.875rem;">
                            <strong>📌 Payment Window Opened</strong><br>
                            After completing payment on PayStack, close the payment window or click the "X" button. Then click the "Verify Now" button above.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Pass cart data to JavaScript for PayStack integration
        window.cartData = {
            total: <?= $cartTotal ?>,
            items: <?= json_encode($cartItems) ?>,
            currency: 'GHS',
            customerEmail: '<?= htmlspecialchars($customer['customer_email'] ?? '') ?>'
        };
    </script>
    <script src="../js/checkout.js"></script>
</body>
</html>
